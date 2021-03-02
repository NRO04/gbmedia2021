<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNewsTable extends Migration
{

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('news_comments');
        Schema::dropIfExists('news_roles');
        Schema::dropIfExists('news_likes');
        Schema::dropIfExists('news_studios');
        Schema::dropIfExists('comment_likes');
        Schema::dropIfExists('comment_has_comments');
        Schema::dropIfExists('news');
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        /*Schema::connection('external')->create('news', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('body');
            $table->string('file');
            $table->string('extension');
            $table->foreignId('created_by')->references('id')->on('laravel.users');
            $table->boolean('is_shared')->default(0);
            $table->boolean('studio_shared')->default(0);
            $table->timestamps();
        });

        Schema::connection('external')->create('news_comments', function (Blueprint $table) {
            $table->id();
            $table->text('body');
            $table->foreignId('news_id')->references('id')->on('news')->onDelete('cascade');
            $table->foreignId('user_id')->references('id')->on('laravel.users');
            $table->integer('reply_id')->default(0);
            $table->timestamps();
        });

        Schema::connection('external')->create('news_roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('news_id')->references('id')->on('news')->onDelete('cascade');
            $table->foreignId('setting_role_id')->references('id')->on('laravel.setting_roles');
            $table->string('role_name');
            $table->timestamps();
        });

        Schema::connection('external')->create('news_studios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('news_id')->references('id')->on('news')->onDelete('cascade');
            $table->foreignId('studio_id')->references('id')->on('laravel.tenants');
            $table->timestamps();
        });

        Schema::connection('external')->create('news_likes', function (Blueprint $table) {
            $table->id();
            $table->string('action');
            $table->foreignId('news_id')->references('id')->on('news')->onDelete('cascade');
            $table->foreignId('user_id')->references('id')->on('laravel.users');
            $table->timestamps();
        });

        Schema::connection('external')->create('news_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('news_id')->references('id')->on('news')->onDelete('cascade');
            $table->foreignId('user_id')->references('id')->on('laravel.users');
            $table->timestamps();
        });*/

        Schema::connection('external')->disableForeignKeyConstraints();

        Schema::connection('external')->dropIfExists('news');
        Schema::connection('external')->create('news', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('body');
            $table->string('file');
            $table->string('extension');
            $table->boolean('is_shared')->default(0);
            $table->boolean('studio_shared')->default(0);
            $table->timestamps();
        });

        Schema::connection('external')->dropIfExists('news_comments');
        Schema::connection('external')->create('news_comments', function (Blueprint $table) {
            $table->id();
            $table->text('body');
            $table->foreignId('news_id')->references('id')->on('news')->onDelete('cascade');
            $table->integer('user_id');
            $table->integer('studio_id');
            $table->integer('reply_id')->default(0);
            $table->timestamps();
        });

        Schema::connection('external')->dropIfExists('news_roles');
        Schema::connection('external')->create('news_roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('news_id')->references('id')->on('news')->onDelete('cascade');
            $table->integer('role_id');
            $table->string('role_name');
            $table->integer('studio_id');
            $table->timestamps();
        });

        Schema::connection('external')->dropIfExists('news_users');
        Schema::connection('external')->create('news_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('news_id')->references('id')->on('news')->onDelete('cascade');
            $table->integer('user_id');
            $table->integer('status');
            $table->integer('studio_id');
            $table->timestamps();
        });

        Schema::connection('external')->dropIfExists('news_studios');
        Schema::connection('external')->create('news_studios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('news_id')->references('id')->on('news')->onDelete('cascade');
            $table->integer('studio_id');
            $table->integer('created_by');
            $table->timestamps();
        });

        Schema::connection('external')->dropIfExists('news_likes');
        Schema::connection('external')->create('news_likes', function (Blueprint $table) {
            $table->id();
            $table->string('action');
            $table->foreignId('news_id')->references('id')->on('news')->onDelete('cascade');
            $table->integer('user_id');
            $table->integer('studio_id');
            $table->timestamps();
        });

        Schema::connection('external')->dropIfExists('news_views');
        Schema::connection('external')->create('news_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('news_id')->references('id')->on('news')->onDelete('cascade');
            $table->integer('user_id');
            $table->integer('studio_id');
            $table->timestamps();
        });

        Schema::connection('external')->enableForeignKeyConstraints();
    }
}
