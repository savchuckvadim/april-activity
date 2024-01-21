<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSmartsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('smarts', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->string('group');
            $table->string('name');
            $table->string('title');
            $table->unsignedBigInteger('bitrixId')->nullable();
            $table->unsignedBigInteger('entityTypeId'); //134
            // for stage status DT134_
            $table->unsignedBigInteger('forStageId')->nullable(); //DT134_
            $table->unsignedBigInteger('forFilterId')->nullable(); //DYNAMIC_134_  
            $table->unsignedBigInteger('crmId')->nullable();  //T9c_

            $table->unsignedBigInteger('portal_id');
            $table->foreign('portal_id')->references('id')->on('portals')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('smarts');
    }
}
