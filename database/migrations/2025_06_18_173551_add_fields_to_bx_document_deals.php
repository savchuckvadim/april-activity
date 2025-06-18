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
            $table->integer('shadowDealId')->nullable();
            $table->integer('serviceSmartId')->nullable();
            $table->integer('smartId')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bx_document_deals', function (Blueprint $table) {
            $table->dropColumn(['shadowDealId', 'serviceSmartId', 'smartId']);
        });
    }
};
