<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Настройки уровня ВЕНДОРА (April), общие для всех порталов и приложений:
     * исходящий прокси за рубеж (адрес, токен, список хостов) и ключи
     * AI-сервисов приложения (Groq, Cloud.ru, Anthropic для модуля поддержки).
     *
     * Строка = одна настройка: `key` совпадает с именем переменной окружения
     * (EGRESS_PROXY_URL, GROQ_API_KEY, ...), значение из таблицы важнее env,
     * пустая строка/отсутствие — действует env. Секреты (`is_secret`) лежат
     * зашифрованными (AES-256-CBC на APP_SECRET_KEY, как ключи портала) —
     * открытый текст в базу не попадает. Реестр допустимых ключей, тексты
     * админки и проверки значений — в Nest (libs/vendor-settings); новые
     * настройки добавляются без миграций.
     *
     * Читают и пишут Nest-приложения (apps/admin — страница «Настройки
     * приложения»; event-sales, bot и прочие читают с кэшем в минуту).
     * Prisma подхватывает таблицу ручной правкой схемы (db pull запрещён,
     * см. back/prisma/SCHEMA_MAINTENANCE.md).
     */
    public function up(): void
    {
        Schema::create('vendor_settings', function (Blueprint $table) {
            // Имя настройки = имя переменной окружения, которую она перекрывает.
            $table->string('key', 64)->primary();

            // Значение; для секретов — шифртекст формата <iv hex>:<data hex>.
            $table->longText('value')->nullable();

            // Секрет: наружу отдаётся только маска, хранится шифрованным.
            $table->boolean('is_secret')->default(false);

            // Кто менял (логин/почта администратора) — для истории правок.
            $table->string('updated_by')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendor_settings');
    }
};
