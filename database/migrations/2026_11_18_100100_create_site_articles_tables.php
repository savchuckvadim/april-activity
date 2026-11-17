<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * СТАТЬИ САЙТА April (решения владельца 17.11.2026): общие для всех
     * разделов сайта — справочник «AI для ОП», теория процесса продаж,
     * открытый раздел статей и следующие.
     *
     * Три таблицы:
     *  - site_articles          — статья: раздел, тип, адрес, статус, порядок,
     *                             какая версия опубликована и кем;
     *  - site_article_revisions — версии текста: заголовок, надзаголовок,
     *                             описание, поля для поисковиков, блоки (JSON).
     *                             Версия неизменна; черновик — последняя
     *                             версия, если её номер больше опубликованной;
     *  - site_article_media     — где в статье стоит медиа: обложка, место в
     *                             тексте (id блока-скрина) или галерея.
     *                             Одно медиа — в скольких угодно статьях.
     *
     * section и type — строки, перечни в коде Nest (@lib/site-content):
     * раздел — это маршрут и движок отрисовки сайта, без кода новый раздел
     * всё равно не появится, поэтому отдельной таблицы разделов нет.
     * content_format — каким движком сайта рисовать блоки ('theory-v1' —
     * движок process/_core, 'how-v1' — «Как мы работаем»).
     *
     * Главы, у которых текст уже лежит в коде сайта (type = 'chapter'):
     * нет опубликованной версии — сайт показывает текст из кода; первая
     * версия в базе может быть взята из кода кнопкой в админке (source =
     * 'code'). base_code_hash — отпечаток главы из кода, на которой сделана
     * правка: по нему админка видит, что в коде глава обновилась позже.
     *
     * Защита от одновременной правки — номер версии внутри статьи
     * (unique article_id + number): сохранение передаёт ожидаемый номер,
     * расхождение — отказ, повторная вставка того же номера — отказ.
     *
     * Удаления «мягкого» нет: статья уходит в status = 'archived', адрес
     * остаётся занятым, история цела. Удалить навсегда можно только статью,
     * которая ни разу не публиковалась.
     *
     * Читают и пишут Nest-приложения: apps/admin — редактор статей и
     * медиатека; apps/bot — выдача опубликованного сайту.
     * Prisma-схема правится РУКАМИ (back/prisma/schema.prisma, модели
     * SiteArticle, SiteArticleRevision, SiteArticleMedia), db pull запрещён.
     */
    public function up(): void
    {
        Schema::create('site_articles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->timestamps();

            // Раздел сайта: 'ai' | 'process-sales' | 'articles' | ...
            $table->string('section', 32);
            // Тип: 'chapter' | 'article' | 'guide' | 'news' | 'case'
            $table->string('type', 32);
            // Адрес внутри раздела: 'theory/call-analysis'; обзор раздела — 'index'.
            $table->string('slug', 191);
            // 'draft' | 'published' | 'unpublished' | 'archived'
            $table->string('status', 16)->default('draft');
            // Порядок в перечне раздела.
            $table->integer('sort')->default(500);

            // Опубликованная версия; NULL — ни разу не публиковалась.
            // Внешний ключ — ниже, после таблицы версий.
            $table->char('published_revision_id', 36)->nullable();
            $table->timestamp('published_at')->nullable();
            $table->string('published_by', 128)->nullable();

            // Дополнительные свойства статьи, которым не нужна своя колонка
            // (метки, для кого статья и т. п.): состав — в коде Nest, новые
            // свойства добавляются без миграции. NULL — свойств нет.
            $table->json('meta')->nullable();

            // Кто создал и кто менял последним (логин администратора).
            $table->string('created_by', 128)->nullable();
            $table->string('updated_by', 128)->nullable();

            $table->unique(['section', 'slug'], 'site_articles_section_slug_unique');
            // Перечень раздела на сайте и в админке.
            $table->index(['section', 'status', 'sort'], 'site_articles_section_status_sort_index');
            // Индекс под внешний ключ ниже — с понятным именем.
            $table->index('published_revision_id', 'site_articles_published_revision_index');
        });

        Schema::create('site_article_revisions', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->char('article_id', 36);

            // Номер версии внутри статьи: 1, 2, 3 …
            $table->unsignedInteger('number');
            // Откуда версия: 'admin' — правка, 'code' — взята из кода сайта,
            // 'restore' — копия прежней версии.
            $table->string('source', 16)->default('admin');
            // 'theory-v1' | 'how-v1'
            $table->string('content_format', 32);

            $table->string('eyebrow')->nullable();
            $table->string('title');
            $table->text('description')->nullable();
            // Поля для поисковиков; NULL — берутся заголовок и описание.
            $table->string('seo_title')->nullable();
            $table->string('seo_description', 500)->nullable();

            // Блоки статьи; состав проверяет Nest по content_format.
            $table->json('blocks');

            // Отпечаток главы из кода, на которой сделана правка.
            $table->string('base_code_hash', 16)->nullable();
            // Пометка правщика «что поменял».
            $table->string('comment')->nullable();

            // Версия неизменна: только когда и кто создал.
            $table->string('created_by', 128)->nullable();
            $table->timestamp('created_at')->nullable();

            // Объявлен до внешнего ключа: MySQL использует его и для
            // article_id, лишнего индекса не появится.
            $table->unique(['article_id', 'number'], 'site_article_revisions_article_number_unique');
            $table->foreign('article_id')->references('id')->on('site_articles')->onDelete('cascade');
        });

        Schema::table('site_articles', function (Blueprint $table) {
            $table->foreign('published_revision_id')->references('id')->on('site_article_revisions')->onDelete('set null');
        });

        Schema::create('site_article_media', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->timestamps();

            $table->char('article_id', 36);
            $table->char('media_id', 36);

            // 'cover' | 'inline' | 'gallery'
            $table->string('role', 16);
            // Место: у inline — id блока-скрина ('scr-kpi-pulse'), у обложки —
            // 'cover', у галереи — NULL (порядок — sort).
            $table->string('slot', 64)->nullable();
            $table->integer('sort')->default(500);
            // Подпись в этой статье; NULL — подпись медиа по умолчанию.
            $table->string('caption', 500)->nullable();

            $table->string('created_by', 128)->nullable();

            // Одна обложка и одно медиа на место; галерея (slot NULL) — без ограничения.
            $table->unique(['article_id', 'role', 'slot'], 'site_article_media_article_role_slot_unique');
            // «Где используется медиа».
            $table->index('media_id', 'site_article_media_media_id_index');

            $table->foreign('article_id')->references('id')->on('site_articles')->onDelete('cascade');
            // Медиа, стоящее в статье, удалить нельзя.
            $table->foreign('media_id')->references('id')->on('site_media')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('site_article_media');
        Schema::table('site_articles', function (Blueprint $table) {
            $table->dropForeign(['published_revision_id']);
        });
        Schema::dropIfExists('site_article_revisions');
        Schema::dropIfExists('site_articles');
    }
};
