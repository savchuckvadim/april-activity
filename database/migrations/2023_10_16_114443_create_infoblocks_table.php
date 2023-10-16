<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInfoblocksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('infoblocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('infoGroupId');
            $table->timestamps();
            $table->integer('inGroupId');
            $table->string('code');
            $table->string('name');
            $table->string('title');
            $table->longText('description');
            $table->longText('descriptionForSale');
            $table->text('shortDescription');
            $table->string('weight');
            $table->string('isIndependent'); //самостоятельный
            $table->boolean('isFree');


        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('infoblocks');
    }
}
