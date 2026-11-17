<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * МЕДИАТЕКА САЙТА April (решения владельца 17.11.2026): картинки, ролики и
     * внешние видео для статей всех разделов сайта.
     *
     * Строка = одно медиа. Файл лежит на нашем сервере в общем хранилище
     * приложений: storage/public/site-media/library/<id>.<отпечаток>.<расш>
     * (постер ролика — <id>.poster.<отпечаток>.<расш>, тёмный вариант
     * картинки — <id>-dark.<отпечаток>.<расш>). Отпечаток в имени меняется
     * вместе с содержимым, поэтому файл кешируется навсегда, а замена видна
     * сразу. В таблице — описание файла: подпись, alt, размеры, длительность,
     * кто загрузил.
     *
     * kind:
     *  - image — картинка (скрин), файл обязателен;
     *  - video — ролик MP4 на нашем сервере: работает без VPN, перемотка есть.
     *    У того же ролика может быть и внешняя ссылка (embed_*): сайт
     *    показывает свой файл, ссылку — как запасной вариант;
     *  - embed — только внешнее видео (YouTube, Rutube, VK), файла нет.
     * Перечни kind и embed_provider — в коде Nest (@lib/site-content), новые
     * значения добавляются без миграции.
     *
     * Адрес встраивания внешнего видео сайт собирает сам по поставщику и id
     * ролика — произвольный iframe из базы на страницу не попадает.
     *
     * Кадры раздела /platform (storage/public/site-media/platform, имя файла
     * = запись, без базы) этой таблицей не описываются и работают как есть.
     *
     * Мягкого удаления нет: медиа, которое стоит хоть в одной статье, удалить
     * нельзя (внешний ключ restrict в site_article_media); неиспользуемое
     * удаляется вместе с файлами.
     *
     * Читают и пишут Nest-приложения: apps/admin — медиатека и привязка к
     * статьям; apps/bot — выдача сайту (GET api/site-media/library/files/<имя>,
     * сайт отдаёт со своего адреса /media/library/<имя>).
     * Prisma-схема правится РУКАМИ (back/prisma/schema.prisma, model
     * SiteMedia), db pull запрещён.
     */
    public function up(): void
    {
        Schema::create('site_media', function (Blueprint $table) {
            // uuid генерирует Nest; он же — основа имени файла на диске.
            $table->uuid('id')->primary();
            $table->timestamps();

            // 'image' | 'video' | 'embed'
            $table->string('kind', 16);

            // Служебное название в медиатеке; клиент его не видит.
            $table->string('title')->nullable();
            // Подпись под кадром по умолчанию; статья может поставить свою.
            $table->string('caption', 500)->nullable();
            // Текст вместо картинки для незрячих и поисковиков.
            $table->string('alt', 500)->nullable();

            // --- Файл на нашем сервере (у embed — NULL)
            $table->string('file_name', 100)->nullable();
            // Вариант картинки для тёмной темы сайта; NULL — один на обе темы.
            $table->string('dark_file_name', 100)->nullable();
            // Постер ролика — картинка до запуска.
            $table->string('poster_file_name', 100)->nullable();
            $table->string('mime', 64)->nullable();
            $table->unsignedInteger('size_bytes')->nullable();
            $table->unsignedSmallInteger('width')->nullable();
            $table->unsignedSmallInteger('height')->nullable();
            // Длительность ролика, секунд.
            $table->unsignedInteger('duration_sec')->nullable();

            // --- Внешнее видео: поставщик, исходный адрес, id ролика.
            // 'youtube' | 'rutube' | 'vk'
            $table->string('embed_provider', 16)->nullable();
            $table->string('embed_url', 500)->nullable();
            $table->string('embed_id', 64)->nullable();

            // Кто загрузил (логин администратора).
            $table->string('uploaded_by', 128)->nullable();

            // Медиатека в админке: по виду, свежие первыми.
            $table->index(['kind', 'created_at'], 'site_media_kind_created_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('site_media');
    }
};
