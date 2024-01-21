<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTemplateCounterTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('template_counter', function (Blueprint $table) {
            $table->unsignedBigInteger('template_id');
            $table->unsignedBigInteger('counter_id');
            $table->string('value')->nullable();
            $table->string('prefix')->nullable();
            $table->boolean('day')->default(false);
            $table->boolean('year')->default(false);
            $table->boolean('month')->default(false);
            $table->integer('count')->default(0);
            $table->integer('size')->default(1);
            $table->foreign('template_id')->references('id')->on('templates')->onDelete('cascade');
            $table->foreign('counter_id')->references('id')->on('counters')->onDelete('cascade');
            $table->primary(['template_id', 'counter_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('template_counter');
    }
}
