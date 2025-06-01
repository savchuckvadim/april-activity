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
        Schema::create('portal_region', function (Blueprint $table) {
            $table->foreignId('portal_id')->constrained()->onDelete('cascade');
            $table->foreignId('region_id')->constrained()->onDelete('cascade');
            $table->decimal('own_abs', 8, 2)->nullable();
            $table->decimal('own_tax', 8, 2)->nullable();
            $table->decimal('own_tax_abs', 8, 2)->nullable();
            $table->primary(['portal_id', 'region_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('portal_region');
    }
};
