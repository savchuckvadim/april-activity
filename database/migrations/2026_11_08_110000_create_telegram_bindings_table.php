<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Привязка Telegram-чата к человеку на портале.
     *
     * Клиентский Telegram-бот April (один на вендора, libs/cabinet) шлёт
     * только то, что положено ЭТОМУ человеку на ЭТОМ портале: отчёт
     * собственнику, карточки руководителям, алерты. Привязка делается
     * одноразовым кодом из кабинета (во фрейме Битрикса или на сайте):
     * человек пишет боту `/start <код>` — бот сохраняет chat_id.
     *
     * Кого привязали: `bitrix_user_id` — сотрудник портала (из сессии
     * фрейма) и/или `user_id` — учётная запись кабинета (вход с сайта).
     * Один чат может быть привязан к нескольким порталам (собственник
     * с двумя порталами) — уникальность по паре portal + chat.
     * `revoked_at` — отвязка (`/stop`, кабинет, админка) без удаления.
     *
     * Prisma подхватывает таблицу ручной правкой схемы (db pull запрещён,
     * см. back/prisma/SCHEMA_MAINTENANCE.md).
     */
    public function up(): void
    {
        Schema::create('telegram_bindings', function (Blueprint $table) {
            $table->char('id', 36)->primary();

            $table->unsignedBigInteger('portal_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('bitrix_user_id', 32)->nullable();

            // Telegram chat_id (личный чат — положительный, группа — отрицательный).
            $table->bigInteger('chat_id');
            $table->string('telegram_username')->nullable();
            // Имя из Telegram на момент привязки — для списка в админке.
            $table->string('display_name')->nullable();

            // Откуда привязка: frame | site | admin.
            $table->string('created_by')->nullable();

            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();

            $table->foreign('portal_id')
                ->references('id')->on('portals')
                ->onDelete('cascade');
            $table->foreign('user_id')
                ->references('id')->on('users')
                ->onDelete('set null');

            $table->unique(['portal_id', 'chat_id'], 'telegram_bindings_portal_chat_unique');
            $table->index(['portal_id', 'bitrix_user_id'], 'telegram_bindings_portal_bitrix_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('telegram_bindings');
    }
};
