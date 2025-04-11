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
        Schema::create('bitrix_app_secrets', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('group')->nullable();
            $table->string('type')->nullable();
            $table->string('code');
            $table->longText('client_id');    
            $table->longText('client_secret');
           
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bitrix_app_secrets');
    }
};
