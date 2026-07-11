<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Маркетплейс Битрикс24: по-компонентные статусы установки pbx-сущностей.
     *
     * Провижининг конфигурации портала (pbx-install) состоит из множества
     * компонентов (поля сделок/компаний, воронки, смарты, списки, RPA,
     * реквизиты, группы, отделы...). Статус хранится ПО КАЖДОМУ компоненту:
     * экран прогресса установки, ретраи с конкретного места и диагностика.
     *
     * Отдельный случай — компонент недоступен из-за ограничений портала
     * (например, RPA запрещены тарифом Битрикс24): status = unavailable
     * + reason_code = tariff_restriction. Это НЕ ошибка приложения —
     * фронт показывает «недоступно на вашем тарифе», установка продолжается.
     */
    public function up(): void
    {
        Schema::create('marketplace_install_components', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->timestamps();

            $table->uuid('marketplace_install_id');
            $table->foreign('marketplace_install_id')
                ->references('id')->on('marketplace_installs')
                ->onDelete('cascade');

            // денормализация для выборок «все компоненты портала» без join
            $table->unsignedBigInteger('portal_id');
            $table->foreign('portal_id')->references('id')->on('portals')->onDelete('cascade');

            // длины укорочены: колонки входят в составной unique-индекс
            // (utf8mb4 = 4 байта/символ, лимит ключа MySQL — 3072 байта)
            $table->string('product_code', 32); // sales | service
            // тип компонента: deal_fields | deal_categories | company_fields |
            // contact_fields | smart | list | rpa | rq | group | department | task | konstructor | placement ...
            $table->string('component_type', 64);
            // конкретный экземпляр (имя смарта/списка/плейсмента); '' — если тип единственный
            $table->string('component_code', 191)->default('');

            // pending | installing | installed | error | unavailable | skipped
            $table->string('status')->default('pending');
            // причина для error/unavailable: tariff_restriction | scope_missing | bitrix_error | manual_skip
            $table->string('reason_code')->nullable();
            $table->longText('error_detail')->nullable();

            $table->unsignedInteger('attempts')->default(0);
            $table->timestamp('last_attempt_at')->nullable();

            $table->unique(
                ['marketplace_install_id', 'product_code', 'component_type', 'component_code'],
                'mp_install_components_unique'
            );
            $table->index(['portal_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marketplace_install_components');
    }
};
