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
        Schema::create('portal_contracts', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('title');  // Отображаемое имя
            $table->string('template')->nullable();  // для APP
            $table->integer('order')->nullable();
            $table->foreignId('portal_id')->constrained()->onDelete('cascade');
            $table->foreignId('contract_id')->constrained()->onDelete('cascade');
            $table->foreignId('portal_measure_id')->constrained('portal_measure'); // связь с PortalMeasure
            $table->foreignId('bitrixfield_item_id')->constrained('bitrixfield_item_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('portal_contracts');
    }
};
