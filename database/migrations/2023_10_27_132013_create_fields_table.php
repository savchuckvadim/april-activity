<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFieldsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fields', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->integer('number');
            $table->string('name');
            $table->string('code');
            $table->string('type'); //string | array | integer | float | data
            $table->string('isGeneral'); //для всех порталов
            $table->string('isDefault');
            $table->string('isRequired');
            $table->string('value')->nullable();
            $table->longText('description')->nullable();
            $table->string('bitixId')->nullable();
            $table->string('bitrixTemplateId')->nullable();
            $table->string('isActive');
            $table->string('isPlural');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('fields');
    }
}
