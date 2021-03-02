<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBookingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('booking_types', function (Blueprint $table) {
            $table->id();
            $table->string('booking');
            $table->string('type');
            $table->timestamps();
        });

        Schema::create('booking_schedules', function (Blueprint $table) {
            $table->id();
            $table->string('hour');
            $table->string('minutes');
            $table->string('meridiem');
            $table->foreignId('booking_type_id')->references('id')->on('booking_types');
            $table->timestamps();
        });

        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_schedule_id')->references('id')->on('booking_schedules');
            $table->foreignId('booking_type_id')->references('id')->on('booking_types');
            $table->foreignId('user_id')->references('id')->on('users');
            $table->foreignId('model_id')->nullable()->references('id')->on('users');
            $table->foreignId('rescheduled_by')->nullable()->references('id')->on('users');
            $table->boolean('was_rescheduled')->default(0);
            $table->string('nick')->nullable();
            $table->string('status')->default(0);
            $table->string('date_range');
            $table->date('date');
            $table->string('day');
            $table->string('month');
            $table->string('year');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('booking_processes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->references('id')->on('bookings');
            $table->foreignId('user_id')->references('id')->on('users');
            $table->foreignId('model_id')->references('id')->on('users');
            $table->string('booking_type');
            $table->foreignId('booking_type_id')->references('id')->on('booking_types');
            $table->integer('process_status')->default(0);
            $table->string('date_range');
            $table->date('session_date');
            $table->date('submitted_date')->nullable();
            $table->date('review_date')->nullable();
            $table->string('attachment')->nullable();
            $table->timestamps();
        });

        Schema::create('booking_exonerates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('booking_type_id')->constrained();
            $table->timestamps();
        });

        Schema::create('booking_days', function (Blueprint $table) {
            $table->id();
            $table->string('day_name');
            $table->timestamps();
        });

        Schema::create('booking_quarters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_day_id')->references('id')->on('booking_days');
            $table->foreignId('setting_location_id')->references('id')->on('setting_locations');
            $table->foreignId('booking_type_id')->references('id')->on('booking_types');
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
        Schema::dropIfExists('booking_types');
        Schema::dropIfExists('booking_schedules');
        Schema::dropIfExists('bookings');
        Schema::dropIfExists('booking_processes');
        Schema::dropIfExists('booking_exonerated');
        Schema::dropIfExists('booking_days');
        Schema::dropIfExists('booking_quarters');
    }
}
