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
        Schema::create('btx_categories', function (Blueprint $table) {
            $table->id();
            $table->string('entity_type');  // класс сущности родителя (например, BtxDeal, BtxSmart, BtxskTaskGroup)
            $table->unsignedBigInteger('entity_id');  // ID сущности сделка пустая привязывается к порталу чтобы к ней привязать categories и fields
            $table->string('parent_type');  // cold | base - например для смарт или
            // чтобы из всех привязанных категорий к смарту выделить определенного типа
            // например будет smart.ColdCategory(это те у которых parent_type cold)
            $table->timestamps();
            $table->string('type'); //smart deal task lead
            $table->string('group'); //sales service  отдел
            $table->string('title'); //отображаемое имя
            $table->string('name'); //имя в битрикс
            $table->string('bitrixId'); //id в bitrix 23
            $table->string('bitrixCamelId'); //id в bitrix ufCrm
            $table->string('code'); //для доступа из app 
            $table->boolean('isActive');


        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('btx_categories');
    }
};
