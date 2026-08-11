<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Импорт статистики СКАП (Nest apps/event-service, модуль skap).
     *
     * Крон-конвейер забирает выгрузки СКАП (zip / Online.csv /
     * Online_detail.csv / Prime_lent.csv) из папки на Диске Битрикс и
     * создаёт элементы смарт-процесса «СКАП» (элемент = логин клиента за
     * месяц). Эти таблицы — журнал контроля «что уже записали, а что нет»
     * и полный съём данных:
     *
     * - skap_import_files    — журнал файлов с Диска (дедуп по disk_file_id,
     *                          детект перезаливки по disk_updated_at/size);
     * - skap_import_items    — записи логин×месяц (ключ идемпотентности
     *                          dedup_key, связки company/deal/contact/смарт);
     * - skap_sessions        — каждая сессия из Online_detail («вся инфа»);
     * - skap_subscriptions   — комплекты/рассылки из Prime_lent (снапшот);
     * - skap_import_runs     — журнал прогонов (статусы, счётчики, стоп).
     *
     * Читает и пишет ТОЛЬКО Nest. Prisma подхватывает таблицы ручной
     * правкой схемы — уже вписаны (db pull запрещён,
     * см. back/ai/SCHEMA_MAINTENANCE.md). План модуля:
     * back/ai/tasks/skap-import-pipeline-plan.md.
     */
    public function up(): void
    {
        Schema::create('skap_import_files', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->unsignedBigInteger('portal_id');
            $table->foreign('portal_id')->references('id')->on('portals')->onDelete('cascade');
            $table->string('domain'); // дубль домена портала для логов/выборок

            $table->string('disk_file_id', 64);       // ID файла на Диске Битрикс
            $table->string('file_name', 512);         // путь от папки загрузок
            $table->dateTime('disk_updated_at')->nullable(); // UPDATE_TIME — детект перезаливки
            $table->bigInteger('size')->nullable();

            // pending / processing / done / error / error_format / skipped
            $table->string('status', 32);
            $table->string('format_version', 32)->nullable();
            $table->text('error')->nullable();
            $table->json('stats')->nullable();        // счётчики + ворнинги

            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();

            $table->unique(['portal_id', 'disk_file_id'], 'skap_import_files_portal_file_unique');
            $table->index(['portal_id', 'status'], 'skap_import_files_portal_status_index');
        });

        Schema::create('skap_import_items', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->unsignedBigInteger('portal_id');
            $table->foreign('portal_id')->references('id')->on('portals')->onDelete('cascade');
            $table->string('domain');

            // Ключ идемпотентности: {domain}:{clientCard}:{login}:{YYYY-MM}
            $table->string('dedup_key', 255)->unique('skap_import_items_dedup_key_unique');
            $table->string('client_card', 64);        // 61-40762-000004 (рег-лист клиента АРМ)
            $table->string('reg_list', 32);           // 61-40762 (карточка РП)
            $table->string('login', 255);             // email-логин СКАП
            $table->date('period');                   // 1-е число отчётного месяца

            // created / updated / skipped_no_company / skipped_too_old / error
            $table->string('status', 32);
            $table->integer('bitrix_item_id')->nullable(); // элемент смарта «СКАП»
            $table->integer('company_id')->nullable();
            $table->integer('deal_id')->nullable();
            $table->integer('contact_id')->nullable();
            $table->text('warning')->nullable();

            // Статистика месяца (дубль полей смарта — события growth/drop
            // и отчёты из БД без похода в Bitrix)
            $table->integer('session_count')->nullable();
            $table->integer('time_total_min')->nullable();
            $table->integer('ip_count')->nullable();

            $table->uuid('file_id')->nullable();      // последний файл-источник
            $table->foreign('file_id')->references('id')->on('skap_import_files')->nullOnDelete();

            $table->timestamps();

            $table->index(['portal_id', 'status'], 'skap_import_items_portal_status_index');
            $table->index(['portal_id', 'period'], 'skap_import_items_portal_period_index');
            $table->index(['portal_id', 'client_card'], 'skap_import_items_portal_client_index');
        });

        Schema::create('skap_sessions', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->unsignedBigInteger('portal_id');
            $table->foreign('portal_id')->references('id')->on('portals')->onDelete('cascade');
            $table->string('domain');

            // {domain}:{clientCard}:{login}:{startedAt ISO}
            $table->string('dedup_key', 255)->unique('skap_sessions_dedup_key_unique');
            $table->uuid('item_id')->nullable();      // запись логин×месяц
            $table->foreign('item_id')->references('id')->on('skap_import_items')->nullOnDelete();
            $table->string('client_card', 64);
            $table->string('reg_list', 32);
            $table->string('login', 255);
            $table->string('complect_arm_id', 32)->nullable();
            $table->string('complect_type')->nullable();
            $table->dateTime('started_at');           // заход
            $table->dateTime('ended_at')->nullable(); // выход
            $table->integer('duration_sec');
            $table->string('ip', 45)->nullable();

            $table->timestamps();

            $table->index(
                ['portal_id', 'client_card', 'started_at'],
                'skap_sessions_portal_client_started_index'
            );
        });

        Schema::create('skap_subscriptions', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->unsignedBigInteger('portal_id');
            $table->foreign('portal_id')->references('id')->on('portals')->onDelete('cascade');
            $table->string('domain');

            // {domain}:{clientCard}:{complectArmId}:{mailingEmail}:{YYYY-MM}
            $table->string('dedup_key', 255)->unique('skap_subscriptions_dedup_key_unique');
            $table->uuid('item_id')->nullable();
            $table->foreign('item_id')->references('id')->on('skap_import_items')->nullOnDelete();
            $table->string('client_card', 64);
            $table->string('reg_list', 32);
            $table->string('complect_arm_id', 32);
            $table->string('complect_name')->nullable();
            $table->string('supply_kind')->nullable();
            $table->string('city')->nullable();
            $table->string('region')->nullable();
            $table->string('version', 64)->nullable();
            $table->text('content')->nullable();      // «Наполнение комплекта»
            $table->string('manager_name')->nullable();
            $table->string('manager_email')->nullable();
            $table->string('mailing_name')->nullable();
            $table->string('mailing_email')->nullable();
            $table->boolean('is_active');             // «Рассылка по email» = Активна
            $table->date('period');                   // месяц снапшота

            $table->timestamps();

            $table->index(['portal_id', 'period'], 'skap_subscriptions_portal_period_index');
        });

        Schema::create('skap_import_runs', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->unsignedBigInteger('portal_id');
            $table->foreign('portal_id')->references('id')->on('portals')->onDelete('cascade');
            $table->string('domain');

            // running / done / stopped_time_budget / error
            $table->string('status', 32);
            $table->string('stop_reason', 64)->nullable();
            $table->json('stats')->nullable();

            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();

            $table->index(['portal_id', 'status'], 'skap_import_runs_portal_status_index');
            $table->index(['portal_id', 'started_at'], 'skap_import_runs_portal_started_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('skap_import_runs');
        Schema::dropIfExists('skap_subscriptions');
        Schema::dropIfExists('skap_sessions');
        Schema::dropIfExists('skap_import_items');
        Schema::dropIfExists('skap_import_files');
    }
};
