<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDashboard extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dashboard_reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->references('id')->on('users');
            $table->string('title', 255);
            $table->string('description')->nullable();
            $table->dateTime('date_from');
            $table->dateTime('date_to');
            $table->string('time_from', 255);
            $table->string('time_to', 255);
            $table->string('color')->default("#4EFF00");
            $table->string('color2')->default("#FFFDFB");
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
        Schema::dropIfExists('dashboard_reservations');
    }
}
