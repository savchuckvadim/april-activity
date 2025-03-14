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
        Schema::create('report_settings', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('domain')->nullable();
            $table->integer('portalId')->nullable();
            $table->integer('bxUserId')->nullable();
            $table->longText('filter')->nullable();
            $table->longText('filters')->nullable();
            $table->longText('grafics')->nullable();
            $table->longText('department')->nullable();
            $table->longText('date')->nullable();
            $table->longText('dates')->nullable();
            $table->longText('other')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report_settings');
    }
};
