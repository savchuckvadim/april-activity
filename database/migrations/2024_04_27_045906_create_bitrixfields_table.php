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
            $table->string('entity_type');  // класс сущности родителя (например, BitrixList, BitrixSmart)
            $table->unsignedBigInteger('entity_id');  // ID сущности
            $table->string('parent_type');  // название  родительской сущности (list, deal) //надо указывать если есть какие-то разграничения внутри сущности типа как тип field например actions или comments так например File может быть и stamp и signature и доступ должен быть из родетельской модели
            //еще нужно при создании с фронта пользователь присылает parent_type и скрипт может понять 
            // какую модель сделать родительской
            // те Лист в себе содежит ссылку на fields но только на те которые 
            // в себе указывают на то что у них родитель только list или только deal 
            // чтобы сразу ограничивать область поиска не по всем филдам  а только по ограниченному кругу
            // но у меня не могут быть разные филды ссылающиеся на одну модель так что возможно дублирование

            // нужно на тот случай когда у одной модели будут разные группы field, 
            // например для Deal - contractField, complectFields, и т.д
            //
            // Bitrixlist
            // public function fields()
            // {
            //     return $this->morphMany(Bitrixfield::class, 'entity')->where('parent_type', 'list');
            // }
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
