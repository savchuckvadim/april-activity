<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeFieldColumnsToBoolean extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('boolean', function (Blueprint $table) {
            $table->boolean('isGeneral')->change();
            $table->boolean('isDefault')->change();
            $table->boolean('isRequired')->change();
            $table->boolean('isActive')->change();
            $table->boolean('isPlural')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('boolean', function (Blueprint $table) {
            $table->string('isGeneral')->change();
            $table->string('isDefault')->change();
            $table->string('isRequired')->change();
            $table->string('isActive')->change();
            $table->string('isPlural')->change();
        });
    }
}
