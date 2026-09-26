<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * AI-аналитика отдела продаж (april-next/back, Фаза 3, поток П6
     * «сегменты транскрипции»): разбор звонка ссылается на секунды записи
     * (цитата возражения → таймкод), чтобы руководитель открывал запись в
     * нужном месте.
     *
     * Сегменты пишет только автоконвейер call-report при транскрибации:
     * JSON-массив `[{ startSec, endSec, speaker, text }]` (speaker —
     * manager | client | unknown; моно-запись без диаризации даёт unknown).
     * Хранятся здесь, а не в UF-полях смарт-процесса: строка смарта
     * ограничена ~8 КБ, а сегменты длинного звонка — десятки килобайт.
     *
     * Старые строки остаются с NULL и читаются как прежде (без таймкодов).
     * Индекса нет: колонка читается по id строки вместе с текстом.
     */
    public function up(): void
    {
        Schema::table('transcriptions', function (Blueprint $table) {
            // сегменты с таймкодами: [{ startSec, endSec, speaker, text }]
            $table->json('segments')->nullable()->after('text');
        });
    }

    public function down(): void
    {
        Schema::table('transcriptions', function (Blueprint $table) {
            $table->dropColumn('segments');
        });
    }
};
