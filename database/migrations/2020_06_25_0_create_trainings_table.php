<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTrainingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('external')->disableForeignKeyConstraints();

        Schema::connection('external')->dropIfExists('trainings');
        Schema::connection('external')->create('trainings', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->string('cover');
            $table->string('image_url');
            $table->boolean('is_shared')->default(0);
            $table->boolean('has_test')->default(0);
            $table->timestamps();
        });

        Schema::connection('external')->dropIfExists('training_files');
        Schema::connection('external')->create('training_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('training_id')->references('id')->on('trainings')->onDelete('cascade');
            $table->string('video')->nullable();
            $table->string('video_url')->nullable();
            $table->integer('sessions')->default(1);
            $table->timestamps();
        });

        Schema::connection('external')->dropIfExists('training_roles');
        Schema::connection('external')->create('training_roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('training_id')->references('id')->on('trainings')->onDelete('cascade');
            $table->string('role_name');
            $table->integer('setting_role_id');
            $table->integer('studio_id');
            $table->timestamps();
        });

        Schema::connection('external')->dropIfExists('training_questions');
        Schema::connection('external')->create('training_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('training_id')->nullable()->references('id')->on('trainings')->onDelete('cascade');
            $table->string('question_title')->nullable();
            $table->timestamps();
        });

        Schema::connection('external')->dropIfExists('training_options');
        Schema::connection('external')->create('training_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('training_id')->nullable()->references('id')->on('trainings')->onDelete('cascade');
            $table->foreignId('training_question_id')->nullable()->references('id')->on('training_questions')->onDelete('cascade');
            $table->string('option_title')->nullable();
            $table->boolean('correct_answer')->nullable();
            $table->timestamps();
        });

        Schema::connection('external')->dropIfExists('training_completed');
        Schema::connection('external')->create('training_completed', function (Blueprint $table) {
            $table->id();
            $table->foreignId('training_id')->nullable()->references('id')->on('trainings')->onDelete('cascade');
            $table->integer('user_id');
            $table->integer('studio_id');
            $table->dateTime('test_completed')->nullable();
            $table->dateTime('date_completed')->nullable();
            $table->timestamps();
        });
        
        Schema::connection('external')->dropIfExists('training_studios');
        Schema::connection('external')->create('training_studios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('training_id')->references('id')->on('trainings')->onDelete('cascade');
            $table->integer('studio_id');
            $table->integer('created_by');
            $table->enum('status', ['active', 'block'])->default('active');
            $table->timestamps();
        });

        Schema::connection('external')->dropIfExists('training_users');
        Schema::connection('external')->create('training_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('training_id')->references('id')->on('trainings')->onDelete('cascade');
            $table->integer('user_id');
            $table->integer('studio_id');
            $table->enum('status', ['active', 'block'])->default('active');
            $table->timestamps();
        });

        Schema::connection('external')->enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('trainings');
        Schema::dropIfExists('training_files');
        Schema::dropIfExists('training_roles');
        Schema::dropIfExists('training_questions');
        Schema::dropIfExists('training_options');
        Schema::dropIfExists('training_completed');
        Schema::dropIfExists('training_studios');
        Schema::dropIfExists('training_users');
    }
}
