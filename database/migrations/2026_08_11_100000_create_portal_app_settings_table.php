<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Универсальные настройки placement-приложений НА ПОРТАЛ.
     *
     * Одна таблица навсегда: строка = (портал, приложение), все параметры —
     * в JSON-колонке `settings`. Новые приложения и новые ключи настроек
     * добавляются БЕЗ миграций.
     *
     * app_code — НАМЕРЕННО строка, а не enum БД: список приложений
     * расширяется кодом (sales, kpi-sales, event-sales, konstructor, portal
     * для общих настроек, ...). Типизация и реестр допустимых кодов/ключей
     * живут в Nest (portal-lib: PORTAL_APP_SETTINGS_SCHEMA) — там же
     * смысловые дефолты. NULL/отсутствие ключа в JSON = «на портале не
     * задано», приложение берёт дефолт кода.
     *
     * Читает и пишет Nest (apps/admin — CRUD-вкладка «Приложения» карточки
     * портала; приложения читают через кэширующий сервис). Prisma
     * подхватывает таблицу ручной правкой схемы (db pull запрещён,
     * см. back/ai/SCHEMA_MAINTENANCE.md).
     */
    public function up(): void
    {
        Schema::create('portal_app_settings', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->unsignedBigInteger('portal_id');
            $table->foreign('portal_id')->references('id')->on('portals')->onDelete('cascade');
            $table->string('domain'); // дубль домена портала для выборок без join

            // Код приложения: 'portal' (общие), 'sales', 'kpi-sales',
            // 'event-sales', 'konstructor', ... Реестр — в коде Nest.
            $table->string('app_code', 64);

            // Все параметры приложения. Ключи и типы описаны реестром в коде.
            $table->json('settings')->nullable();

            $table->timestamps();

            // Одна строка настроек на пару портал+приложение
            $table->unique(['portal_id', 'app_code'], 'portal_app_settings_portal_app_unique');
            // Приложения читают по домену без join
            $table->index(['domain', 'app_code'], 'portal_app_settings_domain_app_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('portal_app_settings');
    }
};
