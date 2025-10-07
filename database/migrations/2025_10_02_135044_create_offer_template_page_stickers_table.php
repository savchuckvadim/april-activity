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
        Schema::create('offer_template_page_stickers', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('offer_template_page_id')->constrained('offer_template_pages')->cascadeOnDelete();
            $table->integer('order');
            $table->string('name');
            $table->string('code')->nullable();
            $table->string('size');
            $table->string('height');
            $table->string('width');
            $table->longText('position')->nullable();
            $table->longText('style')->nullable();
            $table->longText('settings')->nullable();
            $table->longText('background')->nullable();
            $table->longText('colors')->nullable();
            $table->unsignedBigInteger('image_id')->nullable();
            $table->foreign('image_id')
                ->references('id')
                ->on('offer_template_images')
                ->onDelete('set null'); // связь мягкая


        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('offer_template_page_stickers');
    }
};
