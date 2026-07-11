<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Маркетплейс Битрикс24: журнал входящих событий от Битрикса.
     *
     * ONAPPINSTALL / ONAPPUNINSTALL / ONAPPUPDATE / ONAPPPAYMENT и т.п.
     * Нужен для отладки модерации (что реально прилетело и где упало),
     * разбора инцидентов установки и доказательства идемпотентности.
     * payload пишет nest С МАСКИРОВАНИЕМ токенов (сырые токены не хранить).
     * Без внешних ключей: событие может прийти до создания портала.
     */
    public function up(): void
    {
        Schema::create('bitrix_app_events', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->timestamps();

            $table->string('member_id')->nullable()->index(); // plain (не секрет)
            $table->string('domain')->nullable();
            $table->string('event')->index(); // ONAPPINSTALL | ONAPPUNINSTALL | ...
            $table->string('status')->default('received'); // received | processed | error
            $table->longText('error_detail')->nullable();
            $table->longText('payload')->nullable(); // JSON с маскированными токенами
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bitrix_app_events');
    }
};
