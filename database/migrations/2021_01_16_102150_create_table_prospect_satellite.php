<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableProspectSatellite extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    /*public function up()
    {
        Schema::create('satellite_prospects', function (Blueprint $table) {
            $table->id();
            $table->string('owner', 150);
            $table->string('first_name', 150)->nullable();
            $table->string('second_name', 150)->nullable();
            $table->string('last_name', 150)->nullable();
            $table->string('second_last_name', 150)->nullable();
            $table->string('document_number',50)->nullable();
            $table->string('rut', 150)->nullable();
            $table->string('email', 150);
            $table->string('phone', 150)->nullable();
            $table->string('address', 255)->nullable();
            $table->string('neighborhood', 255)->nullable();
            $table->foreignId('department_id')->nullable()->references('id')->on('global_departments');
            $table->foreignId('city_id')->nullable()->references('id')->on('global_cities');
            $table->integer('studio');
            $table->integer('status');
            $table->text('note',255)->nullable();
            $table->timestamps();
        });

        Schema::create('satellite_prospects_historial', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prospect_id')->nullable()->references('id')->on('satellite_prospect');
            //communication channels = 1 normal, 2 email, 3phone, 4 whatsapp
            $table->integer('type')->default(1);
            $table->text('historial_note',255);
            $table->timestamps();
        });
    }*/

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('satellite_prospects');
        Schema::dropIfExists('satellite_prospects_historial');
    }
}
