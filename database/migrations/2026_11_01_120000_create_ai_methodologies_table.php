<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * МЕТОДИКИ ПРОДАЖ для AI-разбора звонков (решения владельца 01.11.2026):
     * методика — переключаемый конфиг чек-пунктов, якорей шкалы, ограничителей
     * оценки и справочников поверх общего ядра разбора. На портале выбирается
     * одна методика или ни одной (поле методики в настройках AI портала;
     * по умолчанию методики нет).
     *
     * Строка = одна методика одной версии:
     * - `scope` common — общий слой April (например, `polyansky`), виден всем
     *   порталам; portal — своя методика портала `portal_domain` (заводится
     *   формой-брифом в админке);
     * - `code` — код методики (идёт в версии разбора как `code@version`);
     * - `config` — JSON конфига; состав и проверка — в Nest
     *   (libs/call-lib/src/call-report/methodology), новые поля добавляются
     *   без миграций;
     * - `version` меняется при правке пунктов/ограничителей — метка смены
     *   шкалы в разборах (ряд оценок при этом не рвём, решение 01.11.2026);
     * - `is_active` false — методика скрыта из выбора, разборы по ней не идут.
     *
     * Читают и пишут Nest-приложения (event-sales — разбор звонков, admin —
     * раздел «Методики»). Prisma подхватывает таблицу ручной правкой схемы
     * (db pull запрещён, см. back/prisma/SCHEMA_MAINTENANCE.md).
     *
     * Файл лежит в back/ai/tasks/ai-polyansky-method как заготовка:
     * скопировать в online/database/migrations и применить `php artisan migrate`.
     */
    public function up(): void
    {
        Schema::create('ai_methodologies', function (Blueprint $table) {
            // Строковый uuid, как у последних таблиц (vendor_super_users,
            // konstructor_public_link); генерирует Nest при создании строки.
            $table->uuid('id')->primary();

            // Код методики: polyansky, garant-own, … (латиница, дефисы).
            $table->string('code', 64);

            // Название для админки и карточки разбора.
            $table->string('title', 255);

            // Версия конфига; меняется при правке пунктов — метка в разборах.
            $table->string('version', 32)->default('1.0.0');

            // common — общий слой April; portal — своя методика портала.
            $table->enum('scope', ['common', 'portal'])->default('portal');

            // Домен портала для scope = portal; null у общего слоя.
            $table->string('portal_domain', 255)->nullable();

            // Конфиг методики (чек-пункты, якоря, ограничители, справочники).
            $table->json('config');

            // Скрытая методика не предлагается порталам и не применяется.
            $table->boolean('is_active')->default(true);

            // Кто менял (логин администратора) — для истории правок.
            $table->string('updated_by', 128)->nullable();

            $table->timestamps();

            // В одном слое код уникален; у портала — своя копия кода.
            $table->unique(['scope', 'portal_domain', 'code'], 'ai_methodologies_scope_domain_code_unique');
            $table->index('portal_domain');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_methodologies');
    }
};
