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
        Schema::table('btx_deals', function (Blueprint $table) {
            $table->unsignedBigInteger('portal_id')->after('code'); // Добавить после столбца 'code'
            $table->foreign('portal_id')->references('id')->on('portals')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('btx_deals', function (Blueprint $table) {
            $table->dropForeign(['portal_id']);
            $table->dropColumn('portal_id');
        });
    }
};
