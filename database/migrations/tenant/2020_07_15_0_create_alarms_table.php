<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAlarmsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('alarm_status', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('alarms', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('status_id')->references('id')->on('alarm_status');
            $table->date('showing_date');
            $table->integer('cycle_count');
            $table->enum('cycle', ['monthly', 'weekly', 'yearly']);
            $table->boolean('is_fixed_date')->default(0);
            $table->foreignId('finished_by')->nullable()->unsigned();
            $table->date('finished_date')->nullable();
            $table->timestamps();
        });

        Schema::create('alarm_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alarm_id')->references('id')->on('alarms')->onDelete('CASCADE');
            $table->foreignId('user_id')->references('id')->on('users');
            $table->timestamps();
        });

        Schema::create('alarm_roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alarm_id')->references('id')->on('alarms')->onDelete('CASCADE');;
            $table->foreignId('setting_role_id')->references('id')->on('setting_roles');
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
        Schema::dropIfExists('alarm_roles');
        Schema::dropIfExists('alarm_users');
        Schema::dropIfExists('alarms');
        Schema::dropIfExists('alarms_status');
    }
}
