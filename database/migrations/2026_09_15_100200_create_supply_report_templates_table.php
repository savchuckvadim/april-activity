<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Шаблоны отчёта о поставке.
     *
     * Зачем. Генерация отчёта берёт один файл из storage
     * (`konstructor/templates/supply/sales_report.docx`), общий на все порталы;
     * доменного варианта нет ни в базе, ни в админке. Нужны те же привязки,
     * что у остальных документов: портал, поставщик, тип клиента.
     *
     * Плюс своё: состав полей формы отчёта (15 полей — дата продажи, в ОРК,
     * звонок клиенту, в АРМ, оплата, финансы, договор/счёт и т.д.) сейчас
     * зашит в коде. Заказчик просил сделать «поля обязательные и нет —
     * портальные», поэтому:
     *  - `required_fields` — JSON-массив кодов полей, обязательных на портале;
     *  - `optional_fields` — JSON-массив кодов, которые показывать, но не
     *    требовать.
     * Обе колонки NULL = поведение как сейчас (состав и обязательность из
     * кода). Так портал можно настраивать по одному, не ломая остальные.
     *
     * Выбор шаблона на бэке: портал → поставщик (или NULL) → тип клиента
     * (или NULL) → is_default; ничего не нашли — файл из storage, как сегодня.
     *
     * Prisma-схема правится РУКАМИ (back/prisma/schema.prisma,
     * model SupplyReportTemplate).
     */
    public function up(): void
    {
        Schema::create('supply_report_templates', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->unsignedBigInteger('portal_id')->nullable();
            // поставщик (agents.id); NULL — для любого поставщика
            $table->unsignedBigInteger('agent_id')->nullable();
            // JSON-массив кодов типов клиента; NULL — для любого
            $table->longText('client_types')->nullable();

            $table->string('name');
            $table->string('code');
            $table->longText('file_path');
            $table->longText('demo_path')->nullable();
            $table->text('description')->nullable();

            // JSON-массивы кодов полей формы отчёта; NULL — состав из кода
            $table->longText('required_fields')->nullable();
            $table->longText('optional_fields')->nullable();

            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_archived')->default(false);
            $table->timestamp('archived_at')->nullable();
            $table->unsignedBigInteger('creator_bitrix_user_id')->nullable();

            $table->index(['portal_id', 'is_active'], 'supply_report_templates_lookup_index');
            $table->index('agent_id', 'supply_report_templates_agent_id_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supply_report_templates');
    }
};
