<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReferredModelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('referred_models', function (Blueprint $table) {
            $table->id();
            $table->string('first_name')->nullable();
            $table->string('middle_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('second_last_name')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('email')->nullable();
            $table->foreignId('department_id')->references('id')->on('global_departments');
            $table->foreignId('city_id')->references('id')->on('global_cities');
            $table->integer('status')->default(0);
            $table->integer('model_prospect_id')->nullable();
            $table->integer('studio_creator_id')->nullable();
            $table->integer('created_by')->nullable();
            $table->integer('referred_by')->nullable();
            $table->dateTime('referred_date')->nullable();
            $table->integer('converted_studio_id')->nullable();
            $table->dateTime('converted_studio_date')->nullable();
            $table->timestamps();
        });

        Schema::create('referred_model_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('referred_model_id')->references('id')->on('referred_models');
            $table->string('path');
            $table->timestamps();
        });

        Schema::create('referred_model_shared', function (Blueprint $table) {
            $table->id();
            $table->foreignId('referred_model_id')->references('id')->on('referred_models');
            $table->integer('studio_id');
            $table->timestamps();
        });

        Schema::create('referred_model_studios', function (Blueprint $table) {
            $table->id();
            $table->integer('studio_id');
            $table->timestamps();
        });

        Schema::create('referred_model_seen', function (Blueprint $table) {
            $table->id();
            $table->foreignId('referred_model_id')->references('id')->on('referred_models');
            $table->foreignId('user_id')->references('id')->on('users');
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
        Schema::dropIfExists('referred_models');
        Schema::dropIfExists('referred_model_images');
        Schema::dropIfExists('referred_model_shared');
        Schema::dropIfExists('referred_model_studios');
        Schema::dropIfExists('referred_model_seen');
    }
}
