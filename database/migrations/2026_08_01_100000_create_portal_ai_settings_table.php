<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Настройки AI-отчётности по звонкам на КОНКРЕТНЫЙ портал.
     *
     * До этой таблицы весь конвейер (apps/event-sales, модуль call-report)
     * управлялся глобальными env: одна модель, одни пороги, один список
     * пилотных доменов на всех клиентов сразу. Развести клиентов было нельзя.
     *
     * ВАЖНО: все настроечные колонки nullable БЕЗ default. NULL означает
     * «на портале не задано» — тогда Nest берёт значение из своих глобальных
     * env (CALL_REPORT_*). Если поставить булевым тумблерам default(true),
     * этот фолбэк сломается: строка появится и глобальные настройки перестанут
     * действовать. Смысловые дефолты живут в коде, а не в БД.
     *
     * Читает и пишет Nest (apps/admin — админ-ручка, apps/event-sales —
     * резолвер настроек). Prisma подхватывает таблицу ручной правкой схемы
     * (см. back/ai/SCHEMA_MAINTENANCE.md — db pull здесь запрещён).
     */
    public function up(): void
    {
        Schema::create('portal_ai_settings', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->unsignedBigInteger('portal_id');
            $table->foreign('portal_id')->references('id')->on('portals')->onDelete('cascade');
            $table->string('domain'); // дубль домена портала для выборок без join

            // --- Тумблеры (NULL = берём из глобальных env) ---

            // Обрабатывать ли звонки этого портала вообще (главный выключатель)
            $table->boolean('enabled')->nullable();

            // Глубокий разбор: 7 разделов, спич, оценки (CALL_REPORT_DEEP_ANALYSIS_ENABLED)
            $table->boolean('deep_analysis_enabled')->nullable();

            // Создавать элемент смарт-процесса «AI-анализ звонков» (CALL_REPORT_CRON_CREATE_SMART)
            $table->boolean('create_smart_enabled')->nullable();

            // Дешёвая классификация типа звонка (CALL_REPORT_CLASSIFY_ENABLED)
            $table->boolean('classify_enabled')->nullable();

            // Анализировать только менеджеров отдела продаж (CALL_REPORT_SALES_ONLY)
            $table->boolean('sales_only')->nullable();

            // --- Пороги отбора звонков (NULL = из глобальных env) ---

            // Минимальная длительность звонка, сек: короче — не анализируем
            $table->integer('min_duration_sec')->nullable();

            // Окно поиска звонков назад, часов
            $table->integer('window_hours')->nullable();

            // Максимум звонков в очередь за один скан (защита бюджета транскрибации)
            $table->integer('max_per_run')->nullable();

            // Порог реанимации зависших обработок, минут
            $table->integer('stale_minutes')->nullable();

            // --- Частота обработки портала ---
            //
            // Крон в Nest тикает с фиксированной частотой (сейчас раз в 30 минут)
            // и поменять её на портал нельзя: интервал зашит в декоратор @Cron и
            // читается один раз при старте приложения. Поэтому частота на портал
            // реализуется иначе: тик проходит по всем порталам, но пропускает те,
            // у кого с last_scan_at прошло меньше их интервала. Значение меньше
            // частоты самого крона смысла не имеет — чаще тика всё равно не выйдет.

            // Интервал сканирования портала в дневные часы, минут
            $table->integer('scan_interval_minutes')->nullable();

            // Интервал в ночные часы: основную работу дешевле и спокойнее делать
            // ночью, а днём — «по чуть-чуть». NULL = ночь не выделяется.
            $table->integer('night_scan_interval_minutes')->nullable();

            // Границы ночного окна, часы 0-23 по таймзоне портала.
            // Окно может пересекать полночь (например, с 22 до 6).
            $table->unsignedTinyInteger('night_start_hour')->nullable();
            $table->unsignedTinyInteger('night_end_hour')->nullable();

            // Когда портал сканировали в последний раз — по нему считается,
            // пора ли снова. Пишет Nest после каждого успешного скана.
            $table->timestamp('last_scan_at')->nullable();

            // --- Модели (NULL = из глобальных env) ---

            // Модель первичного анализа: резюме и рекомендации (CALL_REPORT_LLM_MODEL)
            $table->string('llm_model')->nullable();

            // Модель глубокого разбора звонка
            $table->string('deep_analysis_model')->nullable();

            // --- Прочее ---

            // ДЕМО-режим портала: bitrix-id сотрудников, чьи звонки анализируем.
            // Заменяет суффикс домена в env CALL_REPORT_DOMAINS (`domain:222|323`).
            // NULL или пустой массив = весь отдел продаж.
            $table->json('allowed_user_ids')->nullable();

            // Задел под параметры, которым не нужна отдельная колонка
            // (добавляются без миграции)
            $table->json('settings')->nullable();

            $table->timestamps();

            // Одна строка настроек на портал
            $table->unique('portal_id', 'portal_ai_settings_portal_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('portal_ai_settings');
    }
};
