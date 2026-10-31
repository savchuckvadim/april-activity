<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Регион-источник своего рабса портала.
     *
     * Зачем. Партнёр часто работает по нескольким регионам по одной цене:
     * берёт себе Алтайский край, а рабс ставит «как у Ленинградской
     * области». До сих пор это делали копией числа в `own_abs`, и после
     * каждого обновления прайса Гаранта такие копии приходилось
     * переписывать руками. Теперь связь portal_region может ссылаться на
     * регион-источник: действующий рабс портала берётся из его `abs`,
     * `tax`, `tax_abs` на момент расчёта, и обновление цен региона
     * подхватывается всеми порталами, которые на него ссылаются.
     *
     * Приоритет при расчёте (back/libs/garant/src/region/lib/portal-price.util.ts):
     * регион-источник → ручной `own_abs` → общий рабс региона.
     * Ручной рабс остаётся для особых цен; задание одного сбрасывает другое.
     *
     * Удаление региона-источника обнуляет ссылку (nullOnDelete), сама
     * привязка портала к региону при этом сохраняется.
     *
     * Prisma-схема правится РУКАМИ (back/prisma/schema.prisma, model portal_region).
     */
    public function up(): void
    {
        Schema::table('portal_region', function (Blueprint $table) {
            $table->foreignId('own_abs_region_id')
                ->nullable()
                ->after('own_tax_abs')
                ->constrained('regions')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('portal_region', function (Blueprint $table) {
            $table->dropConstrainedForeignId('own_abs_region_id');
        });
    }
};
