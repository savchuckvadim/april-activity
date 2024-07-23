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
        Schema::create('contracts', function (Blueprint $table) {
            
            $table->id();
            $table->timestamps();
            $table->string('name');  // имя в битрикс
            $table->integer('number');
            $table->string('title');  // Отображаемое имя
            $table->string('code');  // для APP
            $table->string('type');  // для APP
            $table->string('template')->nullable();  // для APP
            $table->integer('order')->nullable();
            $table->integer('coefficient')->default(1);
            $table->integer('prepayment')->default(1);
            $table->float('discount')->default(1);
            $table->string('productName')->nullable();  // Добавление строки `product`, которая может быть NULL
            $table->string('product')->nullable();  // Добавление строки `product`, которая может быть NULL
            $table->string('service')->nullable();  // Добавление строки `product`, которая может быть NULL

            $table->longText('description')->nullable();  // Добавление строки `product`, которая может быть NULL
            $table->longText('comment')->nullable();  // Добавление строки `product`, которая может быть NULL
            $table->longText('comment1')->nullable();  // Добавление строки `product`, которая может быть NULL
            $table->longText('comment2')->nullable();  // Добавление строки `product`, которая может быть NULL

            $table->boolean('withPrepayment');
        
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contracts');
    }
};
