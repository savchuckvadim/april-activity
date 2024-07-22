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
        Schema::create('portal_measure', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('measure_id')->constrained()->onDelete('cascade');
            $table->foreignId('portal_id')->constrained()->onDelete('cascade');
            $table->string('bitrixId')->nullable(); // Пример дополнительного поля, если необходимо
            $table->string('name')->nullable();  // имя в битрикс
            $table->string('shortName')->nullable();
            $table->string('fullName')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('measure_portal');
    }
};
