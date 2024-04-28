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
        Schema::create('btx_stages', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->unsignedBigInteger('btx_category_id');  // Ссылка на ID поля
            $table->string('name');  // имя в битрикс
            $table->string('title');  // Отображаемое имя
            $table->string('code');  // для APP
            $table->integer('bitrixId');  // id в битриксе
            $table->string('color');  // для APP
            $table->boolean('isActive');

            

            $table->foreign('bitrixfield_id')
                ->references('id')
                ->on('bitrixfields')
                ->onDelete('cascade');
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('btx_stages');
    }
};
