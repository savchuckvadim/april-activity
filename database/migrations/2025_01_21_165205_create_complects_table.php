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
        Schema::create('complects', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('fullName');
            $table->string('shortName');
            $table->longText('description')->nullable();
            $table->string('code');
            $table->string('type');
            $table->string('color');
            $table->float('weight');
            $table->float('abs')->nullable();

            $table->integer('number');
            $table->string('productType');
            $table->boolean('withABS');
            $table->boolean('withConsalting');
         
            $table->boolean('withServices');
            $table->boolean('withLt');
            $table->boolean('isChanging');
            $table->boolean('withDefault');

           


            $table->timestamps();
        });

        // Создаем промежуточную таблицу для связи многие ко многим
        Schema::create('complect_infoblock', function (Blueprint $table) {
            $table->id();
            $table->foreignId('complect_id')->constrained()->onDelete('cascade');
            $table->foreignId('infoblock_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('complect_infoblock');
        Schema::dropIfExists('complects');
    }
};
