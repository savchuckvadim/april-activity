<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Конструктор: публичные ссылки для сотрудников, смотрящих правила
     * формирования комплектов.
     *
     * Ссылку создают и отзывают в админке. По ней Next-приложение konstructor
     * (маршрут /p/{token}) открывает сборку комплекта без Битрикса, пользователя
     * и настроек: КП и документы не формируются, описание комплекта скачать можно.
     *
     * Портал нужен каталогу: договоры, регионы и меры у порталов разные. Наружу
     * его домен не уходит, бэк берёт портал из ссылки. Схема ссылки та же, что у
     * KPI (share_link): неугадываемый токен, срок, отзыв. Таблица своя, потому что
     * share_link требует автора из Битрикса и снимок фильтра, у админки их нет.
     *
     * expires_at = NULL: бессрочная, действует до отзыва.
     *
     * Prisma-схема правится РУКАМИ (back/prisma/schema.prisma, model KonstructorPublicLink).
     */
    public function up(): void
    {
        Schema::create('konstructor_public_link', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->timestamps();

            // urlsafe base64 от randomBytes(24), неугадываемый; и есть «ключ ссылки»
            $table->string('token', 64)->unique();

            $table->unsignedBigInteger('portal_id');
            $table->foreign('portal_id')->references('id')->on('portals')->onDelete('cascade');

            $table->string('title');

            $table->timestamp('expires_at')->nullable();
            $table->timestamp('revoked_at')->nullable();

            // аналитика в админке: ссылку реально открывают?
            $table->integer('view_count')->default(0);
            $table->timestamp('last_viewed_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('konstructor_public_link');
    }
};
