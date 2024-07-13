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
        Schema::create('btx_rpas', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('name');  // имя в битрикс
            $table->string('title');  // Отображаемое имя
            $table->string('code');  // для APP
            $table->string('type');  // для APP
            $table->string('image')->nullable();  // для APP 
            $table->unsignedBigInteger('bitrixId')->nullable();
            $table->string('typeId');  // для APP 
            $table->longText('description')->nullable();  // для APP 
            $table->unsignedBigInteger('entityTypeId')->nullable(); //134

            $table->unsignedBigInteger('forStageId')->nullable(); //DT134_

            $table->unsignedBigInteger('forFilterId')->nullable(); //DYNAMIC_134_  
            $table->unsignedBigInteger('crmId')->nullable();  //T9c_
            $table->unsignedBigInteger('portal_id')->after('code'); // Добавить после столбца 'code'
            $table->foreign('portal_id')->references('id')->on('portals')->onDelete('cascade');

            // 'list',
			// 'settings',
			// 'math',
			// 'tick',
			// 'plane',
			// 'piece',
			// 'vacation',

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('btx_rpas');
    }
};
