<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Тексты поставки для договоров и КП — на бэке, шаблонами.
     *
     * Зачем. Легаси-фронт конструктора носил 13 текстов поставки
     * (contractName, contractProp1/2, contractPropSuppliesQuantity,
     * contractPropLoginsQuantity, contractPropComment/Email, quantityForKp,
     * lcontractName, lcontractProp2, lcontractPropComment/Email,
     * acontractName, acontractPropComment) из Firebase и присылал их в
     * payload генерации документов. В `supplies` из них было 7 колонок;
     * остальные 7 добавляются здесь — договор, отчёт о поставке и КП
     * теперь резолвят тексты по `code` поставки на бэке
     * (back/libs/garant/src/supply/services/supply-text.service.ts),
     * а старый payload принимается как есть (совместимость).
     *
     * Почему шаблоны, а не 19 строк с готовыми текстами. Тексты зависят
     * только от количества одновременных доступов (ОД): «…работы двух
     * пользователей», «на 5 одновременных доступов». Произвольное число
     * ОД (X-ОД: код `x_internet_7`) справочной строки не имеет — текст
     * рендерится из шаблона ближайшей справочной поставки того же типа.
     * Плейсхолдеры (рендер — supply-template.util.ts):
     *   {n}                      число ОД: 5
     *   {words} / {Words}        число прописью, муж. род: пять / Пять
     *   {words:gen}              в падеже (nom|gen|dat|acc|ins|pre): пяти
     *   {words:gen:f}            род m|f|n: пяти / двух / одной
     *   {plural:доступ|доступа|доступов}   форма по числу (1 / 2–4 / 5+)
     * Пример: «Установочная версия на {n} {plural:одновременный доступ|
     * одновременных доступа|одновременных доступов} к системе».
     * Строка без плейсхолдеров — обычный текст (Флэш, Локальная, Стандартная).
     *
     * Prisma-схема правится РУКАМИ (back/prisma/schema.prisma, model supplies).
     */
    public function up(): void
    {
        Schema::table('supplies', function (Blueprint $table) {
            $table->text('contractProp1')->nullable()->after('lcontractPropEmail');
            $table->text('contractProp2')->nullable()->after('contractProp1');
            $table->text('contractPropSuppliesQuantity')->nullable()->after('contractProp2');
            $table->text('quantityForKp')->nullable()->after('contractPropSuppliesQuantity');
            $table->text('lcontractProp2')->nullable()->after('quantityForKp');
            $table->text('acontractName')->nullable()->after('lcontractProp2');
            $table->text('acontractPropComment')->nullable()->after('acontractName');
        });
    }

    public function down(): void
    {
        Schema::table('supplies', function (Blueprint $table) {
            $table->dropColumn([
                'contractProp1',
                'contractProp2',
                'contractPropSuppliesQuantity',
                'quantityForKp',
                'lcontractProp2',
                'acontractName',
                'acontractPropComment',
            ]);
        });
    }
};
