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
        Schema::create('offer_template_fonts', function (Blueprint $table) {
            //связан с шаблоном
            $table->id();
            $table->timestamps();
            $table->foreignId('offer_template_id')->constrained('offer_templates')->cascadeOnDelete();
            $table->string('name');
            $table->string('code');
            $table->longText('data')->nullable();
            $table->longText('items')->nullable();
            $table->longText('current')->nullable();
            $table->longText('settings')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('offer_template_fonts');
    }
};
