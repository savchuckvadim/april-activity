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
        Schema::create('bitrix_settings', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
    
            // Полиморфная связь
            $table->unsignedBigInteger('settingable_id');
            $table->string('settingable_type');
    
            // Поля настройки
            $table->string('type')->nullable();         // text, checkbox, number, json и т.п.
            $table->string('code');                     // уникальный ключ настройки
            $table->string('status')->nullable();       // активна / неактивна
            $table->string('title')->nullable();        // заголовок для UI
            $table->text('description')->nullable();    // описание
            $table->longText('value')->nullable();          // значение
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bitrix_settings');
    }
};
