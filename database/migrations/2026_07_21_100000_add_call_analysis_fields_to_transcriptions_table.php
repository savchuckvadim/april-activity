<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * AI-отчётность по звонкам (april-next/back, libs/call-lib): поля для
     * cron-сканера звонков и дедупликации повторной обработки.
     *
     * Почему НЕ unique(domain, activity_id): в таблице уже есть легитимные
     * дубликаты по этой паре — менеджер может вручную перетранскрибировать
     * один и тот же звонок (на 2026-07-21 в БД до 4 строк на одну активность).
     * Жёсткий unique уронил бы миграцию на существующих данных и сломал бы
     * ручной флоу повторной транскрибации.
     *
     * Поэтому дедуп-ключ вынесен в отдельную nullable-колонку dedup_key
     * ("{domain}:{activity_id}"), которую заполняет ТОЛЬКО автоматический
     * конвейер (cron-сканер). Ручные транскрибации пишут NULL — а NULL в
     * MySQL-unique не конфликтует, сколько бы их ни было. Автоконвейер же
     * получает честную гарантию «один звонок обработан один раз» на уровне
     * БД (upsert по dedup_key), а не best-effort SELECT перед INSERT.
     *
     * call_id / call_started_at — денормализация из voximplant.statistic.get:
     * CALL_ID нужен для будущей склейки нескольких звонков в один разговор и
     * сверки с телефонией, call_started_at — для выборок «звонки за период»
     * и поиска связанных записей отчётов по времени без похода в Bitrix.
     */
    public function up(): void
    {
        Schema::table('transcriptions', function (Blueprint $table) {
            // ключ идемпотентности автоконвейера; ручные транскрибации — NULL
            $table->string('dedup_key', 191)->nullable()->unique()->after('activity_id');

            // идентификатор звонка телефонии (voximplant CALL_ID)
            $table->string('call_id')->nullable()->after('dedup_key');
            // фактическое время начала звонка (CALL_START_DATE)
            $table->timestamp('call_started_at')->nullable()->after('call_id');

            // выборки сканера/Agent API: кандидаты по порталу и статусу,
            // реанимация зависших status='processing'
            $table->index(['domain', 'status']);
            // поиск всех транскрибаций активности (включая ручные дубликаты)
            $table->index(['domain', 'activity_id']);
        });
    }

    public function down(): void
    {
        Schema::table('transcriptions', function (Blueprint $table) {
            $table->dropIndex(['domain', 'status']);
            $table->dropIndex(['domain', 'activity_id']);
            $table->dropUnique(['dedup_key']);
            $table->dropColumn(['dedup_key', 'call_id', 'call_started_at']);
        });
    }
};
