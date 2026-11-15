<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Конструктор: тексты сопроводительного письма КП.
     *
     * Раньше письмо портала жило в шаблоне `offer` из `templates`/`fields`
     * (поле `letter`, текст в `description`). Новому конструктору весь base
     * template не нужен, поэтому письма — своя таблица.
     *
     * bx_user_id = NULL — письмо портала; задан — личное письмо менеджера.
     * is_default — какое письмо подставлять по умолчанию (своё у портала и у
     * каждого менеджера). Текст хранит прежнюю разметку: <color>, <red>, <bold>.
     *
     * legacy_field_id — из какого поля `fields` перенесено письмо: перенос
     * идёт временной ручкой админки, повторный запуск не плодит копий.
     *
     * id — uuid (генерирует бэк), bx_user_id и legacy_field_id — обычный int:
     * Nest не переводит их из bigint. bigint только у portal_id — под внешний
     * ключ на portals.id.
     *
     * Prisma-схема правится РУКАМИ (back/prisma/schema.prisma, model OfferLetter).
     */
    public function up(): void
    {
        Schema::create('offer_letters', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->timestamps();

            $table->unsignedBigInteger('portal_id');
            $table->foreign('portal_id')->references('id')->on('portals')->onDelete('cascade');

            $table->unsignedInteger('bx_user_id')->nullable();

            $table->string('name');
            $table->longText('text');

            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('sort')->default(0);

            $table->unsignedInteger('legacy_field_id')->nullable()->unique();

            $table->index(['portal_id', 'bx_user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('offer_letters');
    }
};
