<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Акции (акционные комплекты) и видимость заготовок конструктора.
     *
     * Зачем. В `bx_document_deals` уже лежат ТРИ вида одного и того же слепка
     * конструктора, различаясь только адресом:
     *  - слепок сделки: dealId + serviceSmartId IS NULL;
     *  - вариант КП: dealId + smartId (элемент смарта «Варианты комплекта»);
     *  - избранное: isFavorite = true + portalId + userId, без сделки
     *    (FavoriteController::getFavorites/store пишет и читает именно эту
     *    таблицу, а не `deal_document_favorites` — та осталась от старой схемы).
     *
     * Акция — четвёртый вид того же слепка: заготовка, которую менеджер не
     * набирает сам, а берёт готовой («зашли, выбрали Избранное или Акции и
     * сразу работают, можно подкорректировать»). Поэтому отдельная таблица не
     * нужна: формат слепка тот же, применение в конструктор — тот же код, что
     * у избранного. Не хватает только четырёх вещей, их и добавляем.
     *
     * Что добавляем:
     *  - `kind` — вид заготовки: deal | variant | favorite | promotion.
     *    Раньше вид выводился из комбинации колонок (isFavorite + dealId +
     *    smartId), что нечитаемо и не даёт индекса. Существующие строки
     *    остаются с NULL — старый код продолжает работать по isFavorite;
     *    заполнять kind будет только новый код.
     *  - `visibility` — кому видно: user | portal | all. Акция «для всех
     *    порталов» (visibility = all) живёт с portalId IS NULL: её заводит
     *    администратор, видят менеджеры любого портала.
     *  - `isActive` / `isArchived` / `archivedAt` — «Акции можно в архив,
     *    скрыть таким образом видимость у менеджеров». Архив, а не удаление:
     *    прошлогодняя акция должна оставаться в истории сделок, где её брали.
     *  - `parentId` — акция может быть НАБОРОМ вариантов, а не одним
     *    комплектом. Родительская строка — сама акция, дочерние — её варианты;
     *    ссылка на ту же таблицу, как варианты ссылаются на сделку.
     *  - `creatorBxUserId` — кто завёл (нужен для «isHead через кабинет»:
     *    руководитель заводит акцию на портал, менеджер видит, но не правит).
     *
     * Колонки `promotion`, `promotionName`, `target`, `group`, `clientGroup`,
     * `clientType`, `code`, `order`, `title` в таблице УЖЕ есть — под акции
     * их и заводили, дописывать не требуется.
     *
     * Таблицу пишут два приложения — Laravel (garant-app.ru) и Nest
     * (api.konstructor.april-app.ru). Добавление nullable-колонок обратно
     * совместимо: оба продолжают работать, пока не начнут их читать.
     *
     * Prisma-схема в back/prisma/schema.prisma правится РУКАМИ — соответствующие
     * поля добавлены в model BxDocumentDeal этой же правкой.
     */
    public function up(): void
    {
        Schema::table('bx_document_deals', function (Blueprint $table) {
            // deal | variant | favorite | promotion
            $table->string('kind', 32)->nullable()->after('isFavorite');
            // user | portal | all
            $table->string('visibility', 32)->nullable()->after('kind');
            $table->boolean('isActive')->default(true)->after('visibility');
            $table->boolean('isArchived')->default(false)->after('isActive');
            $table->timestamp('archivedAt')->nullable()->after('isArchived');
            // набор вариантов акции: ссылка на родительскую строку этой же таблицы
            $table->unsignedBigInteger('parentId')->nullable()->after('archivedAt');
            $table->integer('creatorBxUserId')->nullable()->after('parentId');

            // выборка «покажи менеджеру портала действующие акции»
            $table->index(
                ['portalId', 'kind', 'isArchived'],
                'bx_document_deals_kind_index'
            );
            // выборка «варианты этой акции»
            $table->index('parentId', 'bx_document_deals_parent_id_index');
        });
    }

    public function down(): void
    {
        Schema::table('bx_document_deals', function (Blueprint $table) {
            $table->dropIndex('bx_document_deals_kind_index');
            $table->dropIndex('bx_document_deals_parent_id_index');
            $table->dropColumn([
                'kind',
                'visibility',
                'isActive',
                'isArchived',
                'archivedAt',
                'parentId',
                'creatorBxUserId',
            ]);
        });
    }
};
