<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Конструктор: печать, подписи и логотип поставщика для документов.
     *
     * Раньше картинки поставщика жили в общей таблице `files` (morph к `rqs`,
     * вид файла в `parent_type`, подпись бухгалтера — по `code`). Порядок
     * записей там не задан, формат и размер не проверяются, путь к файлу бывает
     * двух видов. Новому конструктору нужна явная роль картинки и проверенный
     * файл, поэтому у картинок поставщика своя таблица.
     *
     * rq_id — реквизиты поставщика (`rqs.id`): печать и подписи принадлежат
     * юридическому лицу. Документ знает поставщика по `agents.id`, реквизиты
     * находятся через `agents.rqId`.
     *
     * role — роль картинки в документе: stamp (печать), signature_director
     * (подпись руководителя), signature_accountant (подпись главного
     * бухгалтера), logo (логотип). Перечень — в коде Nest
     * (@lib/portal-lib/konstructor, provider-image), новые роли добавляются без
     * миграции. На одни реквизиты — одна картинка каждой роли: замена файла
     * обновляет ту же запись.
     *
     * path — путь внутри `storage/app` (без корня хранилища), например
     * `konstructor/provider-images/15/3f2a….png`: запись не зависит от того,
     * где на сервере лежит хранилище. Файл кладёт Nest, имя — uuid записи.
     *
     * format, width, height, size — что Nest определил по самому файлу при
     * загрузке (png или jpg, пиксели, байты); по ним админка показывает
     * карточку, а PDF знает пропорции без чтения файла.
     *
     * legacy_file_id — из какой записи `files` перенесена картинка: перенос
     * идёт временной ручкой админки, повторный запуск копий не плодит.
     *
     * id — uuid (генерирует Nest). bigint только у rq_id — под внешний ключ на
     * rqs.id; остальные числа — обычный int, Nest не переводит их из bigint.
     *
     * Читают и пишут Nest-приложения: apps/admin — загрузка и замена картинок
     * в карточке поставщика; apps/konstructor — печать в PDF-КП (и в Word, если
     * портал это разрешил настройкой).
     * Prisma-схема правится РУКАМИ (back/prisma/schema.prisma, model
     * ProviderImage), db pull запрещён.
     */
    public function up(): void
    {
        Schema::create('provider_images', function (Blueprint $table) {
            // uuid генерирует Nest; он же — имя файла на диске.
            $table->uuid('id')->primary();
            $table->timestamps();

            $table->unsignedBigInteger('rq_id');
            $table->foreign('rq_id')->references('id')->on('rqs')->onDelete('cascade');

            // 'stamp' | 'signature_director' | 'signature_accountant' | 'logo'
            $table->string('role', 32);

            // Путь внутри storage/app, без корня хранилища.
            $table->string('path', 500);
            // 'png' | 'jpg' — определяется по содержимому файла.
            $table->string('format', 8);
            $table->unsignedInteger('width');
            $table->unsignedInteger('height');
            // Размер файла в байтах.
            $table->unsignedInteger('size');
            // Имя файла, с которым его загрузили: только для показа в админке.
            $table->string('original_name')->nullable();

            $table->unsignedInteger('legacy_file_id')->nullable()->unique();

            $table->unique(['rq_id', 'role']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('provider_images');
    }
};
