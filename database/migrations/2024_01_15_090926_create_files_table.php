<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('files', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('name');
            $table->string('code');
            $table->string('type'); //img text
            $table->string('path'); // генерируется после определения файла на сервере
            $table->string('parent'); //portal rq field april
            $table->string('parent_type')->nullable();
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->string('availability'); //public | local

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('files');
    }
}
