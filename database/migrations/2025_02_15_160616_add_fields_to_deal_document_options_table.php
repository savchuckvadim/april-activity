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
        Schema::table('deal_document_options', function (Blueprint $table) {
            $table->string('domain')->nullable();
            $table->integer('portalId')->nullable();
            $table->integer('bxUserId')->nullable();
            $table->integer('offer_template_id')->nullable();
            $table->integer('actionId')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('deal_document_options', function (Blueprint $table) {
            $table->dropColumn([
                'domain',
                'portalId',
                'bxUserId',
                'offer_template_id',
                'actionId'
            ]);
        });
    }
};
