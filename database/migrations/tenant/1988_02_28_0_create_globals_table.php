<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGlobalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('global_countries', function (Blueprint $table) {
            $table->id();
            $table->string('code', 2)->index();
            $table->string('name', 75);
            $table->timestamps();
        });

        Schema::create('global_departments', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('global_cities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->references('id')->on('global_departments');
            $table->string('name');
            $table->boolean('status')->default(1);
            $table->timestamps();
        });

        Schema::create('global_blood_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 7);
            $table->timestamps();
        });

        Schema::create('global_documents', function (Blueprint $table) {
            $table->id();
            $table->string('name',50);
            $table->string('name_simplified',50);
            $table->boolean('is_listed')->default(1);
            $table->timestamps();
        });

        Schema::create('global_banks', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->default(0);
            $table->timestamps();
        });

        Schema::create('global_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('status');
            $table->bigInteger('value');
            $table->timestamps();
        });

        Schema::create('global_type_contracts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('global_eps', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('status')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('global_countries');
        Schema::dropIfExists('global_departments');
        Schema::dropIfExists('global_cities');
        Schema::dropIfExists('global_blood_types');
        Schema::dropIfExists('global_documents');
        Schema::dropIfExists('global_banks');
        Schema::dropIfExists('global_statuses');
        Schema::dropIfExists('global_type_contracts');
    }
}
