<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStatisticsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('statistics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('satellite_account_id')->references('id')->on('satellite_accounts');
            $table->foreignId('user_id')->references('id')->on('users');
            $table->foreignId('setting_page_id')->references('id')->on('setting_pages');
            $table->foreignId('setting_location_id')->references('id')->on('setting_locations');
            $table->decimal('value')->nullable();
            $table->string('range')->nullable();
            $table->date('date')->nullable();
            $table->timestamps();
        });

        Schema::create('statistics_summary', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->references('id')->on('users');
            $table->decimal('value');
            $table->decimal('goal');
            $table->decimal('record');
            $table->string('range');
            $table->timestamps();
        });

        Schema::create('statistics_commission', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->references('id')->on('users');
            $table->foreignId('setting_location_id')->references('id')->on('setting_locations');
            $table->string('commision_type');
            $table->decimal('total');
            $table->date('date');
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
        Schema::dropIfExists('statistics');
        Schema::dropIfExists('statistics_summary');
    }
}
