<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSchedulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->integer('mon')->unsigned()->default(0);
            $table->integer('tue')->unsigned()->default(0);
            $table->integer('wed')->unsigned()->default(0);
            $table->integer('thu')->unsigned()->default(0);
            $table->integer('fri')->unsigned()->default(0);
            $table->integer('sat')->unsigned()->default(0);
            $table->integer('sun')->unsigned()->default(0);
            $table->foreignId('setting_location_id')->constrained();
            $table->integer('session');
            $table->timestamps();
        });

        Schema::create('schedule_sessions', function (Blueprint $table) {
            $table->id();
            $table->integer('session');
            $table->foreignId('setting_location_id')->constrained();
            $table->integer('available');
            $table->string('shift_start', 50);
            $table->string('shift_end', 50);
            $table->string('working_time', 50);
            $table->string('break', 50);
            $table->timestamps();
        });

        Schema::create('schedule_session_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
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
        Schema::dropIfExists('schedules');
        Schema::dropIfExists('schedule_sessions');
        Schema::dropIfExists('schedule_session_types');
    }
}
