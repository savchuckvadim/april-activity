<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Маркетплейс Битрикс24: идентичность и допуск портала.
     *
     * member_id — постоянный идентификатор портала в Битрикс24 (домен портала
     * может меняться, member_id — нет). ВАЖНО: хранится ОТКРЫТЫМ ТЕКСТОМ
     * (НЕ шифровать на стороне nest!) — это не секрет (приходит в каждом
     * iframe-POST), а уникальный индекс по шифротексту не работает.
     *
     * Все колонки аддитивные и nullable — существующее поведение не меняется.
     */
    public function up(): void
    {
        Schema::table('portals', function (Blueprint $table) {
            $table->string('member_id')->nullable()->unique(); // plain, НЕ шифровать
            $table->string('source')->default('legacy'); // legacy | marketplace
            $table->string('approval_status')->nullable(); // pending | approved | blocked (null = legacy, трактуется как approved)
            $table->timestamp('approved_at')->nullable();
            $table->string('approved_by')->nullable(); // кто одобрил (логин/идентификатор администратора вендора)
        });
    }

    public function down(): void
    {
        Schema::table('portals', function (Blueprint $table) {
            $table->dropUnique(['member_id']);
            $table->dropColumn('member_id');
            $table->dropColumn('source');
            $table->dropColumn('approval_status');
            $table->dropColumn('approved_at');
            $table->dropColumn('approved_by');
        });
    }
};
