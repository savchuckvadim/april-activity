<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Маркетплейс Битрикс24: жизненный цикл тиражной установки приложения.
     *
     * Маркетплейс-мир полностью самостоятелен и НЕ использует
     * bitrix_apps/bitrix_tokens (те остаются чисто легаси для локальных
     * приложений: Client -> User -> Portal -> apps -> tokens).
     * Здесь живёт ВСЯ установка: статусы, scope, версия, лицензия Маркета
     * И токены установки (access/refresh/application_token — per-portal;
     * общие client_id/client_secret приложения — в bitrix_app_secrets).
     *
     * Одна строка = одна установка маркетплейс-приложения на портал
     * (уникальность portal_id + app_code). Удаление приложения клиентом
     * (ONAPPUNINSTALL) — это uninstalled_at (soft), строка не удаляется.
     */
    public function up(): void
    {
        Schema::create('marketplace_installs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->timestamps();

            $table->unsignedBigInteger('portal_id');
            $table->foreign('portal_id')->references('id')->on('portals')->onDelete('cascade');

            // необязательный мостик к легаси-записи bitrix_apps (для миграции клиентов)
            $table->unsignedBigInteger('bitrix_app_id')->nullable();
            $table->foreign('bitrix_app_id')->references('id')->on('bitrix_apps')->onDelete('set null');

            $table->string('app_code'); // код приложения (garant_manager)
            $table->string('domain')->nullable(); // текущий домен портала на момент установки (денормализация для отладки)

            // state machine установки: pending | tokens_stored | events_bound |
            // placements_bound | bx_finished | provisioning | installed | error
            $table->string('install_status')->default('pending');
            $table->string('error_step')->nullable();
            $table->longText('error_detail')->nullable();

            $table->longText('scope')->nullable(); // актуальные права (из ONAPPINSTALL/ONAPPUPDATE)
            $table->string('version')->nullable(); // версия приложения на портале (ONAPPUPDATE)
            $table->string('lang', 10)->nullable(); // LANG портала при установке

            // лицензия Маркета (app.info): STATUS F/D/T/P, PAYMENT_EXPIRED, DAYS
            $table->string('license_status', 1)->nullable();
            $table->boolean('payment_expired')->nullable();
            $table->integer('license_days')->nullable();
            $table->timestamp('license_checked_at')->nullable();

            // токены установки (per-portal, выдаются Битриксом при установке);
            // все шифруются на стороне nest
            $table->longText('access_token')->nullable();
            $table->longText('refresh_token')->nullable();
            $table->timestamp('expires_at')->nullable(); // истечение access_token
            // токен для проверки подлинности событий Битрикса; шифруется на стороне nest
            $table->longText('application_token')->nullable();

            $table->timestamp('installed_at')->nullable();
            $table->timestamp('uninstalled_at')->nullable(); // ONAPPUNINSTALL = soft-delete

            $table->unique(['portal_id', 'app_code']);
            $table->index('install_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marketplace_installs');
    }
};
