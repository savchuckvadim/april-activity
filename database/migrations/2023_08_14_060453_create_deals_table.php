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
            $table->foreignId('portalId');
            $table->integer('dealId')->nullable();
            $table->integer('userId')->nullable();
            $table->string('domain')->nullable();
            $table->string('dealName')->nullable();
            $table->text('app')->nullable();
            $table->text('global')->nullable();
            $table->text('currentComplect')->nullable();
            $table->text('od')->nullable();
            $table->text('result')->nullable();
            $table->text('contract')->nullable();
            $table->text('product')->nullable();
            $table->text('rows')->nullable();

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
