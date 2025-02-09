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
        Schema::create('infoblock_package', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')->constrained('infoblocks')->onDelete('cascade');
            $table->foreignId('infoblock_id')->constrained('infoblocks')->onDelete('cascade');
            $table->timestamps();

            // Уникальная пара, чтобы избежать дублирования связей
            $table->unique(['package_id', 'infoblock_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('infoblock_package');
    }
};
