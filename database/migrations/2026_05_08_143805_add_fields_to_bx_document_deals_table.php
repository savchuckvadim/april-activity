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
        Schema::table('bx_document_deals', function (Blueprint $table) {
            $table->bigInteger('offerSmartId')->nullable();
            $table->longText('ltOther')->nullable();
            $table->longText('iskraConfig')->nullable();//json subtitles counters
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bx_document_deals', function (Blueprint $table) {
            $table->dropColumn(['offerSmartId', 'ltOther', 'iskraConfig']);
        });
    }
};
