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
        Schema::create('offer_template_images', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
         
            $table->string('path');
            $table->enum('storage_type', ['app', 'public', 'private'])->default('public');
            $table->string('original_name')->nullable();
            $table->string('mime')->nullable();
            $table->string('size');
            $table->string('height');
            $table->string('width');
            $table->longText('position')->nullable();
            $table->longText('style')->nullable();
            $table->longText('settings')->nullable();
            $table->boolean('is_public')->default(false); // 🚩 ключевое поле
            $table->enum('parent', ['template','page', 'block', 'sticker', 'other'])->default('other');
            $table->string('bitrix_user_id')->nullable();
            $table->string('domain')->nullable();
            $table->unsignedBigInteger('portal_id')->nullable();
            $table->foreign('portal_id')->references('id')->on('portals')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('offer_template_images');
    }
};
