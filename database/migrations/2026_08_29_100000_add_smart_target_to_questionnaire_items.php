<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Носитель ответа «элемент смарта» для вопроса анкеты.
     *
     * Зачем колонки, а не JSON `meta`. Анкета научилась писать ответ не
     * только в компанию/сделку/лид, но и в ЭЛЕМЕНТ СМАРТА, который создаёт
     * или закрывает поток события (презентация, звонок по решению). Элемент
     * выбирать негде — его знает поток, — а вот СМАРТ у вопроса должен быть
     * записан постоянно: по нему компиляция каталога узнаёт поток, а сверка
     * привязок — в каком носителе искать поле. Раньше носитель поля жил
     * только в теле сохранения (`fieldSource`) и в БД не попадал вовсе,
     * поэтому отличить поле смарта от поля сделки после сохранения было
     * НЕЧЕМ — ровно поэтому привязка к смарту и была запрещена.
     *
     * В `meta` этот адрес класть нельзя: там его никто не валидирует, а по
     * нему идут выборки и фильтрация компиляции.
     *
     * Привязка МЯГКАЯ, без внешнего ключа на `smarts` — как и привязка к
     * полю: переустановка смарта на портале не должна ронять анкету.
     * Смарта нет в списке портала — пункт просто не попадает в каталог.
     *
     * `target_entity` уже `string(16)` — значение 'smart' влезает без
     * изменения колонки.
     *
     * Prisma-схема правится РУКАМИ (db pull запрещён, см.
     * back/ai/SCHEMA_MAINTENANCE.md).
     */
    public function up(): void
    {
        Schema::table('portal_questionnaire_items', function (Blueprint $table) {
            // Строка `smarts` НАШЕЙ БД (не идентификатор Битрикса).
            $table->unsignedBigInteger('smart_id')->nullable()->after('dto_path');
            // Слепок smarts.entity_type_id на момент привязки: расхождение с
            // живым значением означает переустановку смарта — повод для
            // предупреждения, а не для тихой записи в чужой тип.
            $table->unsignedBigInteger('smart_entity_type_id')->nullable()->after('smart_id');

            $table->index(['portal_id', 'smart_id'], 'portal_questionnaire_items_smart_index');
        });
    }

    public function down(): void
    {
        Schema::table('portal_questionnaire_items', function (Blueprint $table) {
            $table->dropIndex('portal_questionnaire_items_smart_index');
            $table->dropColumn(['smart_id', 'smart_entity_type_id']);
        });
    }
};
