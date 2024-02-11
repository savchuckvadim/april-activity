<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIdsToSmartsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('smarts', function (Blueprint $table) {
            $table->timestamps();
            $table->string('forStage')->nullable(); //DT134_
            $table->string('forFilter')->nullable(); //DYNAMIC_134_  
            $table->string('crm')->nullable();  //T9c_

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('smarts', function (Blueprint $table) {
            $table->dropColumn(['forStage', 'forFilter', 'crm']);
        });
    }
}
