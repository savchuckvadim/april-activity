<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('supplies', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('name');
            $table->string('fullName');
            $table->string('shortName');
            $table->text('saleName_1')->nullable();
            $table->text('saleName_2')->nullable();
            $table->text('saleName_3')->nullable();
            $table->integer('usersQuantity');
            $table->longText('description')->nullable();
            $table->string('code');
            $table->string('type');
            $table->string('color')->nullable();
            $table->float('coefficient');
            $table->text('contractName')->nullable();
            $table->text('contractPropComment')->nullable();
            $table->text('contractPropEmail')->nullable();
            $table->text('contractPropLoginsQuantity')->nullable();
            $table->text('lcontractName')->nullable();
            $table->text('lcontractPropComment')->nullable();
            $table->text('lcontractPropEmail')->nullable();


        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplies');
    }
};
