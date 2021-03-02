<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMonitoringsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('monitoring', function (Blueprint $table) {
            $table->id();
            $table->foreignId('model_id')->references('id')->on('users');
            $table->foreignId('monitor_id')->nullable()->references('id')->on('users');
            $table->foreignId('assigned_by')->nullable()->references('id')->on('users');
            $table->string('range');
            $table->date('date');
            $table->integer('status')->default(0);
            $table->integer('step')->nullable();
            $table->foreignId('setting_location_id')->references('id')->on('setting_locations');
            $table->timestamps();
        });

        Schema::create('monitoring_status', function (Blueprint $table) {
            $table->id();
            $table->string('answer');
            $table->integer('value');
            $table->timestamps();
        });

        Schema::create('monitoring_qualifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('monitoring_id')->references('id')->on('monitoring');

            $table->foreignId('look')->nullable()->references('id')->on('monitoring_status');
            $table->foreignId('hairstyle')->nullable()->references('id')->on('monitoring_status');
            $table->foreignId('makeup')->nullable()->references('id')->on('monitoring_status');
            $table->foreignId('lingerie')->nullable()->references('id')->on('monitoring_status');
            $table->foreignId('manicure_pedicure')->nullable()->references('id')->on('monitoring_status');
            $table->longText('comment_on_general')->nullable();

            $table->foreignId('smiles')->nullable()->references('id')->on('monitoring_status');
            $table->foreignId('visual_contact')->nullable()->references('id')->on('monitoring_status');
            $table->foreignId('posture')->nullable()->references('id')->on('monitoring_status');
            $table->foreignId('lures_users')->nullable()->references('id')->on('monitoring_status');
            $table->foreignId('highlights_attributes')->nullable()->references('id')->on('monitoring_status');
            $table->foreignId('hide_flaws')->nullable()->references('id')->on('monitoring_status');
            $table->foreignId('takes_recommendations')->nullable()->references('id')->on('monitoring_status');
            $table->foreignId('interacts_online')->nullable()->references('id')->on('monitoring_status');
            $table->foreignId('fulfills_user_wishes')->nullable()->references('id')->on('monitoring_status');
            $table->foreignId('uses_mic')->nullable()->references('id')->on('monitoring_status');
            $table->longText('comment_on_show')->nullable();

            $table->integer('room_number')->nullable();
            $table->foreignId('room_equipment')->nullable()->references('id')->on('monitoring_status');
            $table->foreignId('room_lighting')->nullable()->references('id')->on('monitoring_status');
            $table->foreignId('room_cleanliness')->nullable()->references('id')->on('monitoring_status');
            $table->foreignId('camera')->nullable()->references('id')->on('monitoring_status');
            $table->foreignId('audio')->nullable()->references('id')->on('monitoring_status');
            $table->foreignId('music')->nullable()->references('id')->on('monitoring_status');
            $table->foreignId('setting_location_id')->nullable()->references('id')->on('setting_locations');
            $table->longText('comment_on_room')->nullable();

            $table->longText('comment_on_model')->nullable();
            $table->foreignId('room_status')->nullable()->references('id')->on('monitoring_status');
            $table->longText('comment_room_status')->nullable();
            $table->longText('comment_on_score')->nullable();
            $table->integer('show_score')->nullable();
            $table->longText('recommendations')->nullable();
            $table->timestamps();
        });

        Schema::create('monitoring_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('monitoring_qualification_id')->references('id')->on('monitoring_qualifications');
            $table->string('report_image');
            $table->timestamps();
        });

        Schema::create('monitoring_archives', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->references('id')->on('users');
            $table->foreignId('monitoring_id')->references('id')->on('monitoring')->onDelete('cascade');
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
        Schema::dropIfExists('monitoring');
        Schema::dropIfExists('monitoring_qualifications');
        Schema::dropIfExists('monitoring_images');
        Schema::dropIfExists('monitoring_archives');
    }
}
