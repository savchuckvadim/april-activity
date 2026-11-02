<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Скидка в процентах в КП на следующий год (решение владельца 02.11.2026).
     *
     * В админке теперь задают процент, а сумма в рублях считается от стоимости
     * услуг: изменилась цена услуги — скидка пересчиталась сама. Клиент видит и
     * процент, и рубли.
     *
     * Старая колонка `adjustment` (рубли со знаком) остаётся: по ней работают
     * ссылки, выданные до процентов. Если процент больше нуля, рубли не
     * применяются, иначе скидка посчиталась бы дважды.
     *
     * ПОРЯДОК ВЫКЛАДКИ: миграция применяется ДО выкладки бэка. Prisma читает
     * все колонки модели, и без этой колонки публичная страница предложения и
     * раздел «КП на год» в админке начнут падать.
     *
     * Prisma-схема правится РУКАМИ (back/prisma/schema.prisma,
     * model RenewalOfferLink), db pull запрещён.
     */
    public function up(): void
    {
        Schema::table('renewal_offer_links', function (Blueprint $table) {
            // 0 — без скидки, 100 — услуги бесплатно. Для действующих ссылок 0:
            // их скидка в рублях продолжает работать через adjustment
            $table->unsignedTinyInteger('discount_percent')->default(0)->after('dev_hour_price');
        });
    }

    public function down(): void
    {
        Schema::table('renewal_offer_links', function (Blueprint $table) {
            $table->dropColumn('discount_percent');
        });
    }
};
