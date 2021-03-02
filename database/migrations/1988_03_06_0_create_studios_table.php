<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStudiosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('studios', function (Blueprint $table) {
            $table->id();
            $table->string('studio_name', 255);
            $table->string('url', 255)->nullable();
            $table->string('db_name', 255)->nullable();
            $table->string('db_user', 255)->nullable();
            $table->string('db_passcode', 255)->nullable();
            $table->boolean('status')->default(0);
            $table->bigInteger('owner_studio_id')->nullable();
            $table->bigInteger('rooms_control_code')->nullable();
            $table->string('unique_code')->nullable();
            $table->string('support_db_name')->nullable();
            $table->string('support_db_user')->nullable();
            $table->string('support_db_passcode')->nullable();
            $table->string('support_url')->nullable();
            $table->boolean('should_share')->default(0);
            $table->timestamps();
        });

        Schema::create('studios_last_login', function (Blueprint $table) {
            $table->id();
            $table->foreignId('studio_id')->references('id')->on('studios');
            $table->foreignId('user_id')->references('id')->on('users');
            $table->ipAddress('ip');
            $table->string('user_agent');
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
        Schema::dropIfExists('studios');
    }
}
