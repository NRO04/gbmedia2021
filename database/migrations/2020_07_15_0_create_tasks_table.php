<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTasksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->integer('created_by_type');
            $table->integer('created_by');
            $table->integer('cafeteria_type_id')->nullable();
            $table->string('title' , 150);
            $table->integer('status')->unsigned()->default(0);
            $table->dateTime('should_finish');
            $table->integer('terminated_by');
            $table->string('code' , 50);
            $table->timestamps();
        });

        Schema::create('task_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained();
            $table->foreignId('user_id')->constrained();
            $table->text('comment' , 255);
            $table->timestamps();
        });

        Schema::create('task_comment_attachments', function (Blueprint $table) {
            $table->id();
            $table->integer('task_comments_id');
            $table->string('file' , 150);
            $table->timestamps();
        });

        Schema::create('task_roles_receivers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained();
            $table->foreignId('setting_role_id')->constrained();
            $table->timestamps();
        });

        Schema::create('task_users_receivers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained();
            $table->foreignId('user_id')->constrained();
            $table->timestamps();
        });

        Schema::create('task_user_status', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained();
            $table->foreignId('user_id')->constrained();
            $table->integer('status')->unsigned()->default(0);
            $table->integer('pulsing')->unsigned()->default(1);
            $table->integer('folder')->unsigned()->default(0);
            $table->timestamps();
        });

        Schema::create('task_user_folders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->string('name' , 150);
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
        Schema::dropIfExists('tasks');
        Schema::dropIfExists('task_comments');
        Schema::dropIfExists('task_comment_attachments');
        Schema::dropIfExists('task_roles_receivers');
        Schema::dropIfExists('task_users_receivers');
        Schema::dropIfExists('task_user_status');
        Schema::dropIfExists('task_user_folder');
    }
}
