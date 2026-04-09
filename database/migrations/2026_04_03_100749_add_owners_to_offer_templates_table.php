<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('offer_templates', function (Blueprint $table) {
            $table->unsignedBigInteger('creator_bitrix_user_id')->nullable();
            $table->boolean('is_archived')->default(false); // вместо удаления переносим в архив
            $table->date('archived_at')->nullable(); // дата архивации - через год все архивы удаляем

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('offer_templates', function (Blueprint $table) {
            $table->dropColumn('creator_bitrix_user_id');
            $table->dropColumn('is_archived');
            $table->dropColumn('archived_at');

        });
    }
};
