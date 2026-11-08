<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Членство учётной записи кабинета клиента (`users`) в портале.
     *
     * Кабинет клиента-портала (Nest, libs/cabinet): учётная запись —
     * строка `users` (email + пароль, организация `client_id`); какие
     * порталы она видит и с какой ролью — здесь. `role`: `owner` — корневой
     * пользователь организации (управляет доступами, передаёт права),
     * `member` — сотрудник с доступом в кабинет. `bitrix_user_id` —
     * ID этого человека на портале Битрикс24: связывает учётку с сессией
     * фрейма (проверяется живым REST-вызовом), по нему же привязывается
     * Telegram. `revoked_at` — доступ снят без удаления строки (история).
     *
     * Строк мало (десятки на портал); все операции — по portal_id.
     * Prisma подхватывает таблицу ручной правкой схемы (db pull запрещён,
     * см. back/prisma/SCHEMA_MAINTENANCE.md).
     */
    public function up(): void
    {
        Schema::create('portal_users', function (Blueprint $table) {
            $table->char('id', 36)->primary();

            $table->unsignedBigInteger('portal_id');
            $table->unsignedBigInteger('user_id');

            // ID сотрудника на портале Битрикс24 (строкой, как в users.bitrix_id).
            $table->string('bitrix_user_id', 32)->nullable();

            // owner | member
            $table->string('role', 16)->default('member');

            // Кто дал доступ: логин администратора April или user:<id>.
            $table->string('invited_by')->nullable();

            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();

            $table->foreign('portal_id')
                ->references('id')->on('portals')
                ->onDelete('cascade');
            $table->foreign('user_id')
                ->references('id')->on('users')
                ->onDelete('cascade');

            $table->unique(['portal_id', 'user_id'], 'portal_users_portal_user_unique');
            $table->index(['portal_id', 'bitrix_user_id'], 'portal_users_portal_bitrix_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('portal_users');
    }
};
