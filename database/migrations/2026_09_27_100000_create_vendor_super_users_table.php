<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Суперпользователи ВЕНДОРА (сотрудники April) на порталах клиентов.
     *
     * Это НЕ настройка портала и НЕ права клиента, поэтому таблица отдельная,
     * а не ключ в `portal_app_settings`: там лежат настройки, которыми владеет
     * клиент, а этими записями распоряжается только April. Владелец портала
     * не должен управлять доступом вендора к своему порталу и видеть его.
     *
     * Не путать с `visibility_all_user_ids` (portal_app_settings, app_code
     * sales): тот поднимает СВОЕГО сотрудника клиента до видимости «все»
     * внутри структуры продаж. Здесь — сотрудник April, который сопровождает
     * портал: видимость all (headOfSource = superuser), «Смотреть как…»,
     * служебные ссылки. Слепую калибровку РОПов (rop-mark) он только
     * смотрит — метки не ставит.
     *
     * Ранее список задавался env `BX_SUPER_USER_IDS` в формате
     * `domain:id[,domain:id...]`; переменная снята, источник правды — эта
     * таблица. Читает Nest (BxSuperUserService), пишет админка (apps/admin).
     *
     * `domain` — намеренный дубль домена портала, чтобы Nest искал по домену
     * из запроса без join (так же сделано в portal_app_settings). Prisma
     * подхватывает таблицу ручной правкой схемы (db pull запрещён,
     * см. back/ai/SCHEMA_MAINTENANCE.md).
     */
    public function up(): void
    {
        Schema::create('vendor_super_users', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->unsignedBigInteger('portal_id');
            $table->foreign('portal_id')->references('id')->on('portals')->onDelete('cascade');
            // Дубль домена портала: Nest ищет по домену из запроса без join.
            $table->string('domain');

            // Bitrix-id сотрудника April на ЭТОМ портале.
            $table->unsignedInteger('bitrix_id');

            // Кто это — для людей в админке («Иванов, внедрение»).
            $table->string('comment')->nullable();

            // Снять доступ, не теряя запись и историю.
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            // Один сотрудник на портале заводится один раз.
            $table->unique(['portal_id', 'bitrix_id'], 'vendor_super_users_portal_bitrix_unique');
            // Горячий путь: «кто суперпользователь на этом домене».
            $table->index(['domain', 'is_active'], 'vendor_super_users_domain_active_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendor_super_users');
    }
};
