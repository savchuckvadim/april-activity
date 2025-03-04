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
        Schema::create('provider_currents', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('domain')->nullable();
            $table->integer('portalId')->nullable();
            $table->integer('bxUserId')->nullable();
            $table->integer('agentId')->nullable();
            $table->integer('providerName')->nullable();            $table->integer('providerName')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('provider_currents');
    }
};
