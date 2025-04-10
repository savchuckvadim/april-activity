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
        Schema::create('bitrix_app_placements', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->unsignedBigInteger('bitrix_app_id'); // связь с BitrixApp
            $table->foreign('bitrix_app_id')->references('id')->on('bitrix_apps')->onDelete('cascade');

            $table->string('code'); //общие названия типа конструктор event_sales и тд
            $table->string('type');
            $table->string('group');
            $table->string('status');
            $table->string('bitrix_heandler');
            $table->string('public_heandler');
            $table->longText('bitrix_codes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bitrix_app_placements');
    }
};
