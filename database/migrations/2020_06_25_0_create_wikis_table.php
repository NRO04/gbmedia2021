<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWikisTable extends Migration
{
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('wiki_categories');
        Schema::dropIfExists('wiki_roles');
        Schema::dropIfExists('wiki_users');
        Schema::dropIfExists('wiki_studios');
        Schema::dropIfExists('wikis');
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::connection('external')->disableForeignKeyConstraints();

        Schema::connection('external')->dropIfExists('wiki_categories');
        Schema::connection('external')->create('wiki_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::connection('external')->dropIfExists('wiki_categories_studios');
        Schema::connection('external')->create('wiki_categories_studios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wiki_category_id')->references('id')->on('wiki_categories')->onDelete('cascade');
            $table->integer('studio_id');
            $table->timestamps();
        });

        Schema::connection('external')->dropIfExists('wikis');
        Schema::connection('external')->create('wikis', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('tag', 100)->nullable();
            $table->longText('body');
            $table->boolean('is_shared')->default(0);
            $table->boolean('status')->default(0);
            $table->foreignId('wiki_category_id')->references('id')->on('wiki_categories')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::connection('external')->dropIfExists('wiki_roles');
        Schema::connection('external')->create('wiki_roles', function (Blueprint $table) {
            $table->id();
            $table->integer('setting_role_id');
            $table->foreignId('wiki_id')->references('id')->on('wikis')->onDelete('cascade');
            $table->integer('studio_id');
            $table->timestamps();
        });

        Schema::connection('external')->dropIfExists('wiki_users');
        Schema::connection('external')->create('wiki_users', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->foreignId('wiki_id')->references('id')->on('wikis')->onDelete('cascade');
            $table->integer('studio_id');
            $table->boolean('status')->default(0);
            $table->timestamps();
        });

        Schema::connection('external')->dropIfExists('wiki_studios');
        Schema::connection('external')->create('wiki_studios', function (Blueprint $table) {
            $table->id();
            $table->integer('studio_id');
            $table->foreignId('wiki_id')->references('id')->on('wikis')->onDelete('cascade');
            $table->foreignId('wiki_category_id')->references('id')->on('wiki_categories')->onDelete('cascade');
            $table->integer('created_by');
            $table->timestamps();
        });

        Schema::connection('external')->enableForeignKeyConstraints();
    }
}
