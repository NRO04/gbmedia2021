<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRoomsControlTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rooms_controls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('setting_location_id')->references('id')->on('setting_locations');
            $table->integer('room_number');
            $table->integer('status');
            $table->timestamp('date');
            $table->foreignId('model_id')->references('id')->on('users');
            $table->foreignId('monitor_id')->references('id')->on('users');
            $table->longText('image')->nullable();
            $table->json('observations')->nullable();
            $table->longText('extra_image')->nullable();
            $table->longText('extra_image_description')->nullable();
            $table->integer('checked_status')->nullable();
            $table->boolean('create_job_flag')->default(0)->nullable();
            $table->boolean('from_synced')->default(0)->nullable();
            $table->timestamps();
        });

        Schema::create('rooms_control_inventories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug');
            $table->string('image')->nullable();
            $table->integer('room_number');
            $table->foreignId('setting_location_id')->references('id')->on('setting_locations');
            $table->integer('position');
            $table->timestamps();

            $table->unique(['slug', 'room_number', 'setting_location_id'], 'unique_item_per_room');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('rooms_controls');
        Schema::dropIfExists('rooms_inventory');
    }
}
