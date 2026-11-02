<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Персональные ссылки на КП на следующий год для действующих клиентов
     * (решения владельца 02.11.2026).
     *
     * Строка = одна ссылка одному клиенту со своими ценами. Клиент открывает
     * `/renewal/{token}` на сайте bitrix; состав услуг и цены приходят из этой
     * строки, а не из адреса, поэтому подменить стоимость в адресной строке
     * нельзя (до 02.11.2026 цены ехали параметрами ссылки — так делать
     * перестали).
     *
     * Цены лежат отдельными колонками: по ним строятся отчёты SQL-ом
     * («скольким клиентам дали скидку», «средний чек по пакету»). Рядом
     * `offer_json` — слепок всего предложения: новые услуги и параметры
     * складываются туда без миграции, а колонки остаются для тех полей, по
     * которым считают. Источник истины для состава — колонки.
     *
     * Срок и отзыв как у konstructor_public_link: expires_at = NULL —
     * действует до отзыва. Состояние (действует / истекла / отозвана)
     * выводится из дат, отдельной колонки нет — рассинхронизироваться нечему.
     * Отозванная, истёкшая и неизвестная ссылки снаружи неразличимы.
     *
     * Читают и пишут Nest-приложения: pbx — публичная ручка по токену,
     * admin — список, выдача и отзыв (раздел «КП на год»).
     *
     * Prisma-схема правится РУКАМИ (back/prisma/schema.prisma,
     * model RenewalOfferLink), db pull запрещён.
     */
    public function up(): void
    {
        Schema::create('renewal_offer_links', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->timestamps();

            // urlsafe base64 от randomBytes(24), неугадываемый: знание токена
            // и есть доступ к предложению
            $table->string('token', 64)->unique();

            // Название клиента в шапке предложения и в списке админки
            $table->string('client_name');

            // Служебная пометка «кому и зачем выдана» — клиент её не видит
            $table->string('title')->nullable();

            // --- Пакет приложений по продажам (Звонки + Конструктор КП + KPI)
            $table->boolean('sales_included')->default(true);
            $table->unsignedInteger('sales_price')->default(5000);

            // --- AI-сервис
            $table->boolean('ai_included')->default(true);
            // Сотрудников у клиента: от этого числа считается цена и коридор
            $table->unsignedSmallInteger('ai_employees')->default(3);
            $table->unsignedInteger('ai_base_price')->default(7500);
            $table->unsignedSmallInteger('ai_base_employees')->default(3);
            // Доплата за каждого сверх базовых
            $table->unsignedInteger('ai_extra_price')->default(1000);
            // Коридор изменения состава без пересчёта цены, человек (±N)
            $table->unsignedSmallInteger('ai_free_delta')->default(2);

            // --- Персональный менеджер
            $table->boolean('manager_included')->default(false);
            $table->unsignedInteger('manager_price')->default(10000);

            // --- Разработка: показывать ли блок, часы в пакете, цена часа сверх
            $table->boolean('dev_included')->default(true);
            $table->unsignedSmallInteger('dev_hours')->default(30);
            $table->unsignedInteger('dev_hour_price')->default(2000);

            // --- Скидка или надбавка к месячному платежу.
            // Со знаком: отрицательная — скидка, поэтому НЕ unsigned
            $table->integer('adjustment')->default(0);
            $table->string('adjustment_label')->default('Скидка');

            // Месячный итог на момент выдачи: чтобы в списке админки и в
            // отчётах видеть сумму, не пересчитывая предложение. Страница
            // всё равно считает итог сама из состава
            $table->unsignedInteger('monthly_total')->default(0);

            // Слепок предложения целиком, включая «другие услуги» строками
            $table->json('offer_json');

            // --- Срок и отзыв
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('revoked_at')->nullable();

            // Аналитика в админке: предложение реально открывают?
            $table->unsignedInteger('view_count')->default(0);
            $table->timestamp('last_viewed_at')->nullable();

            // Кто выдал ссылку (логин администратора) — для истории
            $table->string('created_by', 128)->nullable();

            // Список в админке — свежие первыми
            $table->index('created_at');
            // Поиск по клиенту при повторной выдаче
            $table->index('client_name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('renewal_offer_links');
    }
};
