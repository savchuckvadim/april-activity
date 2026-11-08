<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * История обращений в линию поддержки April — для статистики по
     * порталам клиентов (сводка портала в админке).
     *
     * Монитор поддержки (back: libs/support, крон в apps/bot) на каждом
     * проходе читает ОТКРЫТЫЕ диалоги линии и отмечает их здесь: строка на
     * сессию открытой линии (`line_domain` + `session_id`). Пропал из
     * открытых — `closed_at`. Сколько дольше всего ждал ответа —
     * `max_waited_minutes`; когда оператор взял — `answered_at`.
     *
     * `client_portal` — портал клиента по сайту пользователя Битрикс24-сети
     * (может быть пусто: клиент не указал сайт). Сообщения не хранятся.
     *
     * Prisma подхватывает таблицу ручной правкой схемы (db pull запрещён,
     * см. back/prisma/SCHEMA_MAINTENANCE.md).
     */
    public function up(): void
    {
        Schema::create('support_dialogs', function (Blueprint $table) {
            $table->char('id', 36)->primary();

            // Портал, где живёт открытая линия поддержки (april-dev…).
            $table->string('line_domain');
            $table->unsignedBigInteger('session_id');
            $table->unsignedBigInteger('chat_id');
            $table->unsignedInteger('line_id');

            $table->string('client_portal')->nullable();
            $table->string('client_name')->nullable();
            $table->string('operator_name')->nullable();

            $table->unsignedInteger('max_waited_minutes')->default(0);
            $table->timestamp('first_seen_at')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamp('answered_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();

            $table->unique(['line_domain', 'session_id'], 'support_dialogs_line_session_unique');
            $table->index(['line_domain', 'closed_at'], 'support_dialogs_line_closed_index');
            $table->index(['client_portal', 'first_seen_at'], 'support_dialogs_portal_seen_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('support_dialogs');
    }
};
