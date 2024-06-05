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
        Schema::create('deal_document_favorites', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('dealId')->nullable();   //id модели в online сохраненной сделки - deals
            $table->foreignId('dealDocumentOptionId')->nullable();
            $table->string('domain')->nullable();
            $table->integer('userId')->nullable();
            $table->string('title')->nullable();
            $table->string('complectName')->nullable();
            $table->string('dealName')->nullable();
            $table->text('description')->nullable();
            $table->longText('settings')->nullable();
            $table->string('tag')->nullable();
            $table->string('type')->nullable();
            $table->string('group')->nullable();
            $table->string('promotionName')->nullable();
            $table->string('promotionCode')->nullable();
            $table->string('targetAudience')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deal_document_favorites');
    }
};
