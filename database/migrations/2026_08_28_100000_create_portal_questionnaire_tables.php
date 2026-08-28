<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Портальный каталог АНКЕТ (чек-листов) placement-приложений.
     *
     * Зачем: состав вопросов, которые менеджер заполняет при планировании и
     * при отчёте, сегодня живёт в коде фронта и меняется только релизом.
     * Заранее знать, какие поля заведёт клиент, нельзя — поэтому анкета
     * собирается в админке ИЗ ПОЛЕЙ, которые пользователь создал в Битриксе
     * руками, и хранится здесь.
     *
     * Три таблицы:
     *  - portal_questionnaires      — анкета (назначение, где показывать,
     *                                 условия показа);
     *  - portal_questionnaire_items — вопрос: что спрашиваем, обязательность,
     *                                 тип отображения, В КАКОЕ ПОЛЕ пишем;
     *  - portal_questionnaire_item_options — варианты справочника С bitrixId
     *                                 элемента (ровно как bitrixfield_items:
     *                                 именно этот id уходит в crm.*.update).
     *
     * Привязка к полю — МЯГКАЯ, без FK на `bitrixfields`: поля анкеты заводит
     * пользователь, а переустановка смарта/компании сносит все строки
     * bitrixfields этой сущности (deleteFieldsByEntityId) — анкета молча
     * опустела бы. Якорь — `field_name`: полное UF-имя РОВНО в том виде,
     * в каком его вернул Битрикс (никогда не собирать конкатенацией).
     *
     * Ответов здесь НЕТ: источник правды по ответам — поля CRM, как и сейчас.
     *
     * Читает и пишет Nest: apps/admin — редактор анкет в карточке портала;
     * приложения (event-sales) читают скомпилированный каталог по домену
     * через кэширующий сервис. Prisma подхватывает таблицы РУЧНОЙ правкой
     * схемы (db pull запрещён, см. back/ai/SCHEMA_MAINTENANCE.md).
     */
    public function up(): void
    {
        Schema::create('portal_questionnaires', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->unsignedBigInteger('portal_id');
            $table->foreign('portal_id')->references('id')->on('portals')->onDelete('cascade');
            $table->string('domain'); // дубль домена портала для выборок без join

            // Код приложения: 'event-sales', 'sales', ... Реестр — в коде Nest.
            $table->string('app_code', 64);
            // Стабильный код анкеты внутри приложения: ключ «подтверждено»
            // на фронте. Задаётся при создании и дальше не меняется.
            $table->string('code', 64);

            $table->string('title');
            $table->text('hint')->nullable();

            // 'plan' — анкета ПЛАНИРОВАНИЯ, 'report' — анкета ОТЧЁТНОСТИ.
            $table->string('purpose', 16);
            // Как показываем: 'inline' (карточкой в колонке) | 'modal'.
            $table->string('presentation', 16)->default('inline');
            // Колонка для inline: 'plan' | 'report'.
            $table->string('place', 16)->nullable();
            // Когда пишем ответ: 'onChange' (сразу) | 'onConfirm' (по кнопке).
            $table->string('persist', 16)->default('onChange');

            // Условия показа, И-семантика. JSON, а не таблица: новый вид
            // условия не должен требовать миграции.
            // [{"kind":"planType","values":["refine"]},
            //  {"kind":"reportType","values":["decision"]},
            //  {"kind":"targetStage","values":[...]},
            //  {"kind":"workStatus","values":[...]}, {"kind":"always"}]
            // Неизвестный kind — анкета НЕ показывается (безопасный отказ).
            $table->json('conditions')->nullable();

            // Совместимость с нынешними встроенными наборами:
            // config_key — старый фича-флаг настроек приложения
            // (with_checklist_*), учитывается только если заполнен;
            // legacy_checklist_id — ЗАМЕЩАЕТ одноимённый встроенный набор
            // (не добавляется к нему), чтобы не показать два одинаковых.
            $table->string('config_key', 64)->nullable();
            $table->string('legacy_checklist_id', 64)->nullable();

            $table->boolean('is_active')->default(false);
            $table->integer('sort')->default(500);
            // Инкремент на каждое сохранение: фрейм сверяет свою версию.
            $table->unsignedInteger('version')->default(1);
            $table->unsignedBigInteger('updated_by')->nullable();

            $table->timestamps();

            $table->unique(['portal_id', 'app_code', 'code'], 'portal_questionnaires_portal_app_code_unique');
            $table->index(['domain', 'app_code', 'is_active'], 'portal_questionnaires_domain_app_index');
        });

        Schema::create('portal_questionnaire_items', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->char('questionnaire_id', 36);
            $table->foreign('questionnaire_id')->references('id')->on('portal_questionnaires')->onDelete('cascade');

            // Дубль портала: ответ на «где ещё используется это поле» без join.
            $table->unsignedBigInteger('portal_id');
            $table->foreign('portal_id')->references('id')->on('portals')->onDelete('cascade');

            // Код ВОПРОСА (не поля!) — ключ ответа на фронте. Два разных
            // вопроса могут писать в одно поле и не делить между собой
            // значение, статус сохранения и таймер автосохранения.
            $table->string('code', 64);

            $table->string('title');
            $table->string('placeholder')->nullable();
            $table->text('hint')->nullable();
            // Секция задаётся явно (сегодня группа выводится из префикса кода).
            $table->string('group_title')->nullable();
            $table->integer('sort')->default(500);

            // --- ЧТО СПРАШИВАЕМ (тип отображения) ---
            // 'string'|'text'|'date'|'datetime'|'money'|'enumeration'|'boolean'
            // Реестр допустимых значений и матрица «контрол ↔ тип поля»
            // проверяются НА СОХРАНЕНИИ в Nest.
            $table->string('control', 32);
            // В v1 запрещено валидатором: запись массивов ещё не реализована.
            $table->boolean('is_multiple')->default(false);

            // --- ОБЯЗАТЕЛЬНОСТЬ ---
            $table->boolean('is_required')->default(false);
            // «Обязательность изменения»: пункт закрывается ТОЛЬКО ответом,
            // данным в этой сессии; уже стоящее в CRM значение не считается.
            $table->boolean('require_change')->default(false);
            // Срок годности ответа (только date/datetime): значение старше
            // N дней перестаёт закрывать обязательный пункт.
            $table->integer('stale_after_days')->nullable();

            // --- КУДА ПИШЕМ ---
            // 'crm' — поле сущности, 'dto' — поле отчёта, 'text' — в комментарий.
            $table->string('channel', 16)->default('crm');
            // 'auto' — компания → сделка → лид (текущее поведение),
            // 'entity' — жёстко указанный носитель.
            $table->string('target_mode', 16)->default('auto');
            $table->string('target_entity', 16)->nullable();
            // Для channel='dto': путь в отчёте ('sale.opportunity'),
            // вместо строковых литералов в коде фронта.
            $table->string('dto_path', 64)->nullable();
            // Штатное поле Битрикса (OPPORTUNITY), а не пользовательское.
            $table->boolean('is_native')->default(false);

            // --- ПРИВЯЗКА К ПОЛЮ (мягкая, БЕЗ FK на bitrixfields) ---
            // ГЛАВНЫЙ ЯКОРЬ: полное UF-имя ровно как вернул Битрикс.
            $table->string('field_name')->nullable();
            $table->unsignedBigInteger('field_bitrix_id')->nullable();
            // Вторичный якорь; недоступен, если у ключа портала нет прав
            // администратора CRM (читаем поля через crm.item.fields).
            $table->string('field_xml_id')->nullable();
            // Наш код из pbx-реестра — если поле ставили мы, иначе NULL.
            $table->string('field_code', 64)->nullable();
            // Слепок типа поля на момент привязки: ловим смену типа.
            $table->string('field_type', 32)->nullable();
            // 'ok' | 'missing' | 'type_changed' — по кнопке «Проверить привязки».
            $table->string('field_status', 16)->default('ok');
            $table->timestamp('field_checked_at')->nullable();

            // Расширения без миграций: min/max, rows, маска ввода.
            $table->json('meta')->nullable();

            // Пункт ГАСИМ, не удаляем: иначе уже собранные ответы в CRM
            // становятся необъяснимыми.
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->unique(['questionnaire_id', 'code'], 'portal_questionnaire_items_code_unique');
            $table->index(['questionnaire_id', 'sort'], 'portal_questionnaire_items_sort_index');
            $table->index(['portal_id', 'field_name'], 'portal_questionnaire_items_portal_field_index');
        });

        Schema::create('portal_questionnaire_item_options', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->char('item_id', 36);
            $table->foreign('item_id')->references('id')->on('portal_questionnaire_items')->onDelete('cascade');

            // Наш стабильный код варианта (xmlId элемента, если осмысленный).
            $table->string('code', 64);
            $table->string('title');
            // Именно это значение уходит в crm.*.update — как bitrixfield_items.
            $table->integer('bitrix_id')->nullable();
            $table->string('xml_id')->nullable();
            $table->integer('sort')->default(500);
            $table->boolean('is_default')->default(false);
            // Исчезнувший в Битриксе вариант гасим, а не удаляем.
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->unique(['item_id', 'code'], 'portal_questionnaire_item_options_code_unique');
            $table->index(['item_id', 'bitrix_id'], 'portal_questionnaire_item_options_bitrix_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('portal_questionnaire_item_options');
        Schema::dropIfExists('portal_questionnaire_items');
        Schema::dropIfExists('portal_questionnaires');
    }
};
