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
        Schema::create('offer_template_pages', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('offer_template_id')->constrained('offer_templates')->cascadeOnDelete();
            $table->integer('order');
            $table->string('name');
            $table->string('code')->nullable();
     
            $table->enum('type', ['letter', 'description', 'infoblocks', 'price', 'lt', 'other', 'default'])->default('default');
            $table->boolean('is_active')->default(true);
            
            $table->longText('settings')->nullable(); 
            $table->longText('stickers')->nullable(); 
            $table->longText('background')->nullable(); 
            $table->longText('colors')->nullable();   
            $table->longText('fonts')->nullable();   
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('offer_template_pages');
    }
};
