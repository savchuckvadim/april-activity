<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Индексы под AI-аналитику отдела продаж (back: libs/sales-ai-analytics,
     * apps/kpi-report-sales/src/ai-analytics).
     *
     * Зачем. Таблица `ais` хранит не только разборы звонков, но и снапшоты
     * витрины (provider = app = 'ai-analytics'): обратная связь, настройки,
     * аудит, а с Фазы 2 — недельные/месячные снапшоты менеджеров, модель
     * портала, прогноз, brief, etl-run. Выборки идут по трём осям:
     *  - `transcription_id` — разбор по транскрипции (связь звонок → разбор,
     *    ревизия версий, повестка планёрки);
     *  - `domain + type + activity_id` — снапшот по ключу периода
     *    ('2026-09', '2026-W36', '2026-09-04') или набора настроек
     *    (`findByDomainTypeKeys`), backfill по неделям;
     *  - `domain + type + created_at` — «последнее за N дней» и окна пульса
     *    (`findByDomainTypesInPeriod`).
     * Без индексов каждая из них — полный скан таблицы на портал.
     *
     * Ширина ключа. domain/type/activity_id — VARCHAR(255) utf8mb4:
     * 3 × 1020 = 3060 байт < 3072 (InnoDB DYNAMIC) — префиксы не нужны.
     *
     * Prisma-схема правится РУКАМИ (db pull запрещён, см.
     * back/prisma/SCHEMA_MAINTENANCE.md): @@index добавлены в model Ai.
     */
    public function up(): void
    {
        Schema::table('ais', function (Blueprint $table) {
            $table->index('transcription_id', 'ais_transcription_id_index');
            $table->index(['domain', 'type', 'activity_id'], 'ais_domain_type_activity_id_index');
            $table->index(['domain', 'type', 'created_at'], 'ais_domain_type_created_at_index');
        });
    }

    public function down(): void
    {
        Schema::table('ais', function (Blueprint $table) {
            $table->dropIndex('ais_transcription_id_index');
            $table->dropIndex('ais_domain_type_activity_id_index');
            $table->dropIndex('ais_domain_type_created_at_index');
        });
    }
};
