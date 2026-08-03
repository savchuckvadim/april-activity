<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Собственные креды LLM клиента на портал — чтобы клиент мог подключить
     * свой ключ (Cloud.ru, GigaChat и т.п.), а не работать на общем.
     *
     * Колонки в camelCase намеренно: в этой таблице ключи интеграций уже так
     * названы (nestKey, vibeKey — см. 2026_06_08_124638), а в Nest имена
     * колонок используются напрямую как значения union PortalKeyName.
     *
     * Все три значения шифруются на стороне Nest (PortalKeyCryptoService),
     * как остальные ключи портала. Для baseUrl и имени модели шифрование
     * избыточно, но они намеренно живут в общем наборе: так весь комплект
     * кред читается и пишется одной админ-ручкой admin/portal/{portalId}/keys
     * и одним экраном админки, без разнесения по двум формам.
     */
    public function up(): void
    {
        Schema::table('portals', function (Blueprint $table) {
            $table->text('llmKey')->nullable(); // ключ LLM клиента, шифруется на стороне nest
            $table->text('llmBaseUrl')->nullable(); // endpoint OpenAI-совместимого API клиента
            $table->text('llmModelName')->nullable(); // id модели в каталоге провайдера
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('portals', function (Blueprint $table) {
            $table->dropColumn('llmKey');
            $table->dropColumn('llmBaseUrl');
            $table->dropColumn('llmModelName');
        });
    }
};
