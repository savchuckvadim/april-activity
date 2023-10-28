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
            $table->timestamps();
            $table->integer('number');
            $table->string('name');
            $table->string('title')->nullable();

            $table->longText('description')->nullable();
            $table->longText('descriptionForSale')->nullable();
            $table->text('shortDescription')->nullable();
            $table->string('weight');

            $table->string('code');
            $table->integer('inGroupId');

            $table->foreignId('groupId');


            $table->boolean('isLa');   // относится к судебной практике
            $table->boolean('isFree');
            $table->boolean('isShowing');
            $table->boolean('isSet'); // например пакет ЭР 

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
