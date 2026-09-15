<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Единые привязки шаблонов: поставщик и типы клиента.
     *
     * Требование заказчика: «вообще всё к поставщику должны мочь привязываться,
     * а также можно выбирать, для каких типов клиента доступен (физ, юр,
     * бюджетники, адвокаты)».
     *
     * Сейчас:
     *  - `invoice_templates` уже привязан к поставщику (`agent_id`), но не к
     *    типу клиента;
     *  - `offer_templates` (в нём лежат и страничные шаблоны КП, и word-шаблоны)
     *    не привязан ни к поставщику, ни к типу клиента — только к порталу
     *    через `offer_template_portal`.
     *
     * Добавляем недостающее. Обе колонки nullable, NULL = «годится любому»,
     * поэтому существующие шаблоны продолжают выбираться как раньше.
     *
     * `client_types` — JSON-массив кодов ["org_state","org","ip","fiz"]
     * (ClientTypeEnum на бэке). LongText, а не колонка JSON — как во всех
     * соседних таблицах проекта.
     *
     * Prisma-схема правится РУКАМИ (back/prisma/schema.prisma, модели
     * OfferTemplate и InvoiceTemplate).
     */
    public function up(): void
    {
        Schema::table('offer_templates', function (Blueprint $table) {
            $table->unsignedBigInteger('agent_id')->nullable()->after('code');
            $table->longText('client_types')->nullable()->after('agent_id');

            $table->index('agent_id', 'offer_templates_agent_id_index');
        });

        Schema::table('invoice_templates', function (Blueprint $table) {
            $table->longText('client_types')->nullable()->after('agent_id');
        });
    }

    public function down(): void
    {
        Schema::table('offer_templates', function (Blueprint $table) {
            $table->dropIndex('offer_templates_agent_id_index');
            $table->dropColumn(['agent_id', 'client_types']);
        });

        Schema::table('invoice_templates', function (Blueprint $table) {
            $table->dropColumn('client_types');
        });
    }
};
