<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMaintenancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('maintenance_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('maintenances', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->dateTime('finish_date')->nullable();
            $table->text('comment')->nullable();
            $table->foreignId('status_id')->references('id')->on('maintenance_statuses');
            $table->foreignId('setting_location_id')->references('id')->on('setting_locations');
            $table->timestamps();
        });

        Schema::create('maintenance_alarms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('maintenance_id')->references('id')->on('maintenances');
            $table->foreignId('user_id')->references('id')->on('users');
            $table->boolean('viewed')->default(0);
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
        Schema::dropIfExists('maintenances');
    }
}
