<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDealsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('deals', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->integer('dealId')->nullable();
            $table->integer('userId')->nullable();
            $table->string('domain')->nullable();
            $table->string('dealName')->nullable();
            $table->string('app')->nullable();
            $table->string('global')->nullable();
            $table->string('currentComplect')->nullable();
            $table->string('od')->nullable();
            $table->string('result')->nullable();
            $table->string('contract')->nullable();
            $table->string('product')->nullable();
            $table->string('rows')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('deals');
    }
}
