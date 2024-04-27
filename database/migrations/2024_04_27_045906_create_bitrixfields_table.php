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
        Schema::create('bitrixfields', function (Blueprint $table) {
            $table->id();
            $table->string('entity_type');  // тип сущности (например, BitrixList, BitrixSmart)
            $table->unsignedBigInteger('entity_id');  // ID сущности
            $table->string('parent_type');  // тип родительской сущности (list, field) //надо указывать если есть какие-то разграничения внутри сущности типа как тип field например actions или comments так например File может быть и stamp и signature и доступ должен быть из родетельской модели
            //еще нужно при создании с фронта пользователь присылает parent_type и скрипт может понять 
            // какую модель сделать родительской

            $table->timestamps();
            $table->string('type'); //select, date, string,
            $table->string('title'); //отображаемое имя
            $table->string('name'); //имя в битрикс
            $table->string('bitrixId'); //id в bitrix UF_CRM
            $table->string('bitrixCamelId'); //id в bitrix ufCrm
            $table->string('code'); //для доступа из app например comment или actions и будет list->field where code == actions
            

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bitrixfields');
    }
};
