<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Шаблоны договора.
     *
     * Зачем. У КП (`offer_templates`) и счёта (`invoice_templates`) шаблоны
     * лежат в базе: их видно в админке, можно держать несколько, привязывать к
     * порталу и — у счёта — к поставщику (`agent_id`). У договора и отчёта о
     * поставке базы нет вовсе: генерация берёт ОДИН файл из storage
     * (`konstructor/templates/contract/template.docx`), один на все порталы.
     * Поэтому нельзя ни завести второй договор, ни привязать его к поставщику
     * или типу клиента.
     *
     * Правила привязки (требование заказчика):
     *  - договор привязывается к ТИПУ ДОГОВОРА (`contract_type`: service |
     *    abon | lic | key) — это главное отличие от КП и счёта;
     *  - к поставщику (`agent_id`): NULL = шаблон годится любому поставщику;
     *  - к типам клиента (`client_types`): JSON-массив кодов
     *    ["org_state","org","ip","fiz"]; NULL = годится любому.
     *
     * Выбор шаблона на бэке: портал → поставщик (или NULL) → тип договора →
     * тип клиента (или NULL) → is_default. Нашлось несколько — берём
     * самый специфичный.
     *
     * Почему JSON в LongText, а не колонка JSON: так уже сделано во всех
     * соседних таблицах проекта (`settings`, `tags`, `promotion`,
     * `rules`, `price_settings` в offer_templates) — единообразие важнее.
     *
     * Prisma-схема правится РУКАМИ (back/prisma/schema.prisma, model ContractTemplate).
     */
    public function up(): void
    {
        Schema::create('contract_templates', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->unsignedBigInteger('portal_id')->nullable();
            // поставщик (agents.id); NULL — шаблон для любого поставщика
            $table->unsignedBigInteger('agent_id')->nullable();
            // service | abon | lic | key — CONTRACT_LTYPE
            $table->string('contract_type', 64);
            // JSON-массив кодов типов клиента; NULL — для любого
            $table->longText('client_types')->nullable();

            $table->string('name');
            $table->string('code');
            $table->longText('file_path');
            $table->longText('demo_path')->nullable();
            $table->text('description')->nullable();

            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_archived')->default(false);
            $table->timestamp('archived_at')->nullable();
            $table->unsignedBigInteger('creator_bitrix_user_id')->nullable();

            $table->index(['portal_id', 'contract_type', 'is_active'], 'contract_templates_lookup_index');
            $table->index('agent_id', 'contract_templates_agent_id_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contract_templates');
    }
};
