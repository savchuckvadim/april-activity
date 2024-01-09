<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRqsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rqs', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('name')->nullable();
            $table->string('number')->nullable();
            $table->string('type');  // физ лица / ип / компании
            $table->string('fullname')->nullable();
          
            $table->string('shortname')->nullable();
            $table->string('director')->nullable();    // ФИО директора
            $table->string('position')->nullable();   // должность директора
            $table->string('accountant')->nullable();  //гб

            $table->string('based')->nullable(); // на основании
            $table->string('inn')->nullable();
            $table->string('kpp')->nullable();
            $table->string('ogrn')->nullable();
            $table->string('ogrnip')->nullable();




            $table->string('personName')->nullable();
            $table->string('document')->nullable();
            $table->string('docSer')->nullable();
            $table->string('docNum')->nullable();
            $table->string('docDate')->nullable();
            $table->string('docIssuedBy')->nullable();
            $table->string('docDepCode')->nullable();




            $table->string('registredAdress')->nullable();
            $table->string('primaryAdresss')->nullable();
            $table->string('email')->nullable();
            $table->string('garantEmail')->nullable();
            $table->string('phone')->nullable();
            $table->string('assigned')->nullable();    // Ответственный за получение Справочника
            $table->string('assignedPhone')->nullable();  // его номер

            $table->longText('other')->nullable();
            $table->string('bank')->nullable();
            $table->string('bik')->nullable();
            $table->string('rs')->nullable();
            $table->string('ks')->nullable();
            $table->string('bankAdress')->nullable();
            $table->longText('bankOther')->nullable();


            $table->foreignId('directorSignatureId')->nullable();
            $table->foreignId('accountantSignatureId')->nullable();

            $table->foreignId('stampId')->nullable();
            $table->foreignId('agentId')->nullable();   //providerId or buyerId
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('rqs');
    }
}
