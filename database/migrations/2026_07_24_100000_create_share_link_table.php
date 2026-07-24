<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * KPI-отчёт (kpi-report-sales): публичные ссылки на снимок отчёта.
     *
     * Из Bitrix-фрейма пользователь формирует ссылку на отчёт с зафиксированным
     * фильтром — по ней неавторизованные видят read-only страницу отчёта
     * (Next-апп kpi-sales, роут /share/{token}) со скачиванием Excel.
     *
     * Здесь только МЕТАДАННЫЕ ссылки (владелец, срок, режим обновления,
     * счётчики). Сам снимок данных отчёта лежит в Redis по ключу
     * kpi-share:snapshot:{token} с TTL = expires_at; таблицу читает NestJS
     * (back/apps/kpi-report-sales) через Prisma-интроспекцию.
     *
     * Режимы: необновляемая — фиксированный снимок, любой период фильтра;
     * обновляемая — пересчёт каждые 15 минут от имени портала (cron + Bull),
     * период фильтра не более месяца. Срок жизни любой ссылки — не более
     * 14 дней; протухшая/отозванная редиректит на bitrix.april-app.ru.
     */
    public function up(): void
    {
        Schema::create('share_link', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->timestamps();

            // urlsafe base64 от randomBytes(24), неугадываемый; и есть «ключ ссылки»
            $table->string('token', 64)->unique();

            $table->unsignedBigInteger('portal_id');
            $table->foreign('portal_id')->references('id')->on('portals')->onDelete('cascade');
            $table->string('domain'); // дубль домена портала для выборок без join

            $table->integer('creator_bx_user_id');
            $table->string('creator_name');

            $table->string('title'); // «от {автор}: {период}» либо своё название

            // JSON-снимок фильтра для регенерации и рендера: dateFrom/dateTo,
            // userIds, структура отделов, actions, reportType, merged-selection
            $table->longText('filter_snapshot');

            $table->boolean('is_refreshable')->default(false);
            $table->integer('refresh_interval_sec')->default(900); // 15 минут
            $table->timestamp('last_refreshed_at')->nullable();
            $table->timestamp('next_refresh_at')->nullable(); // для cron-выборки due-ссылок

            $table->timestamp('expires_at'); // created_at + N дней, N <= 14

            $table->string('status')->default('active'); // active | revoked | error

            // аналитика владельцу: ссылку реально смотрят?
            $table->integer('view_count')->default(0);
            $table->timestamp('last_viewed_at')->nullable();

            // список «мои ссылки» в шапке отчёта
            $table->index(['domain', 'creator_bx_user_id']);
            // cron: is_refreshable AND status='active' AND next_refresh_at <= now
            $table->index(['is_refreshable', 'status', 'next_refresh_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('share_link');
    }
};
