<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttendancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attendance_summary', function (Blueprint $table) {
            $table->id();
            $table->foreignId('model_id')->references('id')->on('users');
            $table->string('range');
            $table->date('date');
            $table->integer('worked_days')->default(0);
            $table->integer('unjustified_days')->default(0);
            $table->integer('justified_days')->default(0);
            $table->integer('period')->default(0);
            $table->integer('total_minutes')->default(0);
            $table->integer('total_recovery_minutes')->default(0);
            $table->decimal('goal',10, 2)->default(50.00);
            $table->foreignId('created_by')->references('id')->on('users');
            $table->timestamps();
        });

        Schema::create('attendance_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('status');
            $table->integer('value');
            $table->timestamps();
        });

        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attendance_summary_id')->references('id')->on('attendance_summary');
            $table->foreignId('model_id')->references('id')->on('users');
            $table->string('range');
            $table->date('date');
            $table->foreignId('attendance_type')->references('id')->on('attendance_statuses');
            $table->integer('attendance_minutes')->default(0);
            $table->integer('recovery_minutes')->default(0);
            $table->foreignId('created_by')->references('id')->on('users');
            $table->foreignId('updated_by')->nullable()->references('id')->on('users');
            $table->timestamps();
        });

        Schema::create('attendance_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attendance_id')->references('id')->on('attendances');
            $table->foreignId('attendance_status_id')->references('id')->on('attendance_statuses');
            $table->foreignId('created_by')->references('id')->on('users');
            $table->longText('comment')->nullable();
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
        Schema::dropIfExists('attendance_summary');
        Schema::dropIfExists('attendance_statuses');
        Schema::dropIfExists('attendances');
        Schema::dropIfExists('attendance_comments');
    }
}
