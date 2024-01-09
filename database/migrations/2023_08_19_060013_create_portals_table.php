<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePortalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('portals', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            
            $table->text('domain')->nullable();
            $table->text('key')->nullable(); // key from placement in firebase:  key
            $table->text('C_REST_CLIENT_ID')->nullable(); //from hook in firebase:  clientId
            $table->text('C_REST_CLIENT_SECRET')->nullable(); //from hook in firebase: clientSecret
            $table->text('C_REST_WEB_HOOK_URL')->nullable();   //url from hook in firebase:  hook
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('portals');
    }
}
