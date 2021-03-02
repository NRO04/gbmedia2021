<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCafeteriaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cafeteria_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->time('time');
            $table->time('max_order_time');
            $table->timestamps();
        });

        Schema::create('cafeteria_menus', function (Blueprint $table) {
            $table->id();
            $table->string('description');
            $table->foreignId('cafeteria_type_id')->references('id')->on('cafeteria_types');
            $table->float('price');
            $table->date('date');
            $table->foreignId('created_by')->references('id')->on('users');
            $table->timestamps();
        });

        Schema::create('cafeteria_orders', function (Blueprint $table) {
            $table->id();
            $table->longText('observations')->nullable();
            $table->foreignId('user_id')->references('id')->on('users');
            $table->foreignId('cafeteria_menu_id')->default(0);
            $table->foreignId('location_id')->references('id')->on('setting_locations');
            $table->integer('quantity')->default(1);
            $table->float('total');
            $table->date('date');
            $table->date('payment_date');
            $table->timestamps();
        });

        Schema::create('cafeteria_breakfast_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('cafeteria_breakfast_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->float('price');
            $table->foreignId('cafeteria_breakfast_category_id')->references('id')->on('cafeteria_breakfast_categories')->name('cbf_category_id');
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
        Schema::dropIfExists('cafeteria_types');
        Schema::dropIfExists('cafeteria_menus');
        Schema::dropIfExists('cafeteria_orders');
        Schema::dropIfExists('cafeteria_breakfast_types');
        Schema::dropIfExists('cafeteria_breakfast_categories');
    }
}
