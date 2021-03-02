<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('setting_locations', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->integer('rooms')->unsigned()->default(0);
            $table->integer('position')->unsigned()->default(0);
            $table->integer('base')->unsigned()->default(0);
            $table->string('address')->nullable();
            $table->timestamps();
        });

        Schema::create('setting_location_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('setting_location_id');
            $table->integer('location_id');
            $table->timestamps();
        });

        Schema::create('setting_pages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('type')->nullable();
            $table->integer('position')->nullable();
            $table->integer('admin_status')->nullable();
            $table->string('login')->nullable();
            $table->string('start_week')->nullable();
            $table->string('finish_week')->nullable();
            $table->integer('start_num1')->nullable();
            $table->integer('finish_num1')->nullable();
            $table->integer('start_num2')->nullable();
            $table->integer('finish_num2')->nullable();
            $table->timestamps();
        });

        Schema::create('setting_modules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description');
            $table->boolean('is_admin')->default('0');
            $table->timestamps();
        });
        Schema::create('setting_page_task_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('setting_page_tasks', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description');
            $table->foreignId('page_task_type_id')->references('id')->on('setting_page_task_types');
            $table->foreignId('page_id')->references('id')->on('setting_pages');
            $table->timestamps();
        });

        Schema::create('setting_page_task_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('page_task_id')->references('id')->on('setting_page_tasks');
            $table->string('name');
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
        Schema::dropIfExists('setting__locations');
        Schema::dropIfExists('setting_location_permissions');
        Schema::dropIfExists('setting_pages');
        Schema::dropIfExists('setting_modules');
        Schema::dropIfExists('setting_page_task_options');
        Schema::dropIfExists('setting_page_tasks');
        Schema::dropIfExists('setting_page_task_types');
    }
}
