<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOffersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('offers', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('advertiser_id');
            $table->text('name', 30);
            $table->text('description',300)->nullable();
            $table->string('url')->nullable();
            $table->integer('price')->nullable();
            $table->float('mastersProfit');
            $table->float('appsProfit');
            $table->boolean('isFollowing')->default(0);

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('offers');
    }
   
}
