<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInfoGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('info_groups', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->integer('number');
            $table->string('code');
            $table->string('name');
            $table->string('title');
            $table->longText('description')->nullable();
            $table->longText('descriptionForSale')->nullable();
            $table->text('shortDescription')->nullable();
            $table->string('type');
            $table->string('productType');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('info_groups');
    }
}
