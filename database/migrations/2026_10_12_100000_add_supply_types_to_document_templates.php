<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Третья ось выбора шаблона: ТИП ПОСТАВКИ (internet | proxima).
     *
     * Зачем. Миграции от 15.09 завели у шаблонов две оси — тип договора
     * (`contract_type`) и тип клиента (`client_types`). Но живой Laravel
     * выбирает файл договора по ТРЁМ сегментам пути
     * (`ContractController.php:370`):
     *
     *     konstructor/templates/contract/etalon/{contract_type}/{supply_type}/{client_type}
     *
     * где `$supplyType = $data->supply->type` — независимое значение
     * `internet | proxima` (`ContractController.php:277`), а `{client_type}`
     * — это тип клиента, свёрнутый в две корзины `state | commerc`
     * (`org_state` → `state`, остальные → `commerc`,
     * `ContractController.php:303-309`). То есть `state/commerc` НЕ третья
     * ось, а огрублённый `client_types`, который у нас уже есть и точнее.
     *
     * Без `supply_types` договор услуг «интернет» и договор услуг «проксима»
     * разрешились бы в ОДИН шаблон, хотя сегодня это разные файлы. Примеры
     * из постановки заказчика: «договор услуг проксима», «договор услуг
     * интернет», «лицензионный проксима», «лицензионный интернет» — то есть
     * ось РЕАЛЬНО перемножается с `contract_type`, а не дублирует его.
     *
     * Правила привязки — как у соседних осей:
     *  - JSON-массив кодов `["internet","proxima"]`;
     *  - NULL = шаблон годится любому типу поставки (обратная совместимость:
     *    существующие строки продолжают выбираться как раньше).
     *
     * Приоритет в резолвере (лексикографический кортеж, не сумма весов):
     * портал → поставщик → ТИП ПОСТАВКИ → тип клиента → is_default.
     * `contract_type` в кортеж не входит: для договора это жёсткое равенство,
     * через него резолвер не откатывается (договора «по умолчанию» не бывает).
     *
     * Почему JSON в LongText, а не колонка JSON: так сделано во всех соседних
     * таблицах проекта (`client_types`, `required_fields`, `optional_fields`,
     * `settings`, `tags`) — единообразие важнее.
     *
     * `offer_templates` и `invoice_templates` тоже получают колонку: у КП и
     * счёта тип поставки — НЕОБЯЗАТЕЛЬНЫЙ уточняющий признак (как и тип
     * договора), а не обязательный, как у договора.
     *
     * Prisma-схема правится РУКАМИ (back/prisma/schema.prisma, модели
     * ContractTemplate, SupplyReportTemplate, InvoiceTemplate, OfferTemplate).
     */
    public function up(): void
    {
        Schema::table('contract_templates', function (Blueprint $table) {
            $table->longText('supply_types')->nullable()->after('client_types');
        });

        Schema::table('supply_report_templates', function (Blueprint $table) {
            $table->longText('supply_types')->nullable()->after('client_types');
        });

        Schema::table('invoice_templates', function (Blueprint $table) {
            $table->longText('supply_types')->nullable()->after('client_types');
        });

        Schema::table('offer_templates', function (Blueprint $table) {
            $table->longText('supply_types')->nullable()->after('client_types');
        });
    }

    public function down(): void
    {
        Schema::table('contract_templates', function (Blueprint $table) {
            $table->dropColumn('supply_types');
        });

        Schema::table('supply_report_templates', function (Blueprint $table) {
            $table->dropColumn('supply_types');
        });

        Schema::table('invoice_templates', function (Blueprint $table) {
            $table->dropColumn('supply_types');
        });

        Schema::table('offer_templates', function (Blueprint $table) {
            $table->dropColumn('supply_types');
        });
    }
};
