<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBoutiqueTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('boutique_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('boutique_products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('boutique_category_id')->references('id')->on('boutique_categories');
            $table->text('image')->nullable();
            $table->float('unit_price', 16, 2)->default(0);
            $table->float('wholesaler_price', 16, 2)->default(0);
            $table->enum('nationality', ['Nacional', 'Internacional']);
            $table->boolean('active')->default(1);
            $table->string('barcode')->nullable();
            $table->integer('stock_alarm')->default(0);
            $table->integer('location_alarm')->default(0);
            $table->timestamps();
        });

        Schema::create('boutique_inventories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('boutique_product_id')->references('id')->on('boutique_products');
            $table->foreignId('setting_location_id')->references('id')->on('setting_locations');
            $table->integer('quantity')->default(0);
            $table->timestamps();
        });

        Schema::create('boutique_sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('boutique_product_id')->references('id')->on('boutique_products');
            $table->integer('quantity')->default(1);
            $table->float('total', 16, 2);
            $table->foreignId('setting_location_id')->references('id')->on('setting_locations');
            $table->foreignId('buyer_user_id')->references('id')->on('users');
            $table->foreignId('seller_user_id')->references('id')->on('users');
            $table->timestamps();
        });

        Schema::create('boutique_satellite_sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('boutique_product_id')->references('id')->on('boutique_products');
            $table->integer('quantity')->default(1);
            $table->float('total', 16, 2);
            $table->foreignId('setting_location_id')->references('id')->on('setting_locations');
            $table->foreignId('buyer_owner_id')->references('id')->on('satellite_owners');
            $table->foreignId('seller_user_id')->references('id')->on('users');
            $table->timestamps();
        });

        Schema::create('boutique_logs', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->integer('type_item_id')->nullable();
            $table->foreignId('created_by')->references('id')->on('users');
            $table->text('action');
            $table->ipAddress('ip_address')->nullable();
            $table->timestamps();
        });

        Schema::create('boutique_products_logs', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->foreignId('boutique_product_id')->references('id')->on('boutique_products');
            $table->foreignId('created_by')->references('id')->on('users');
            $table->text('action');
            $table->integer('old_inventory_quantity')->nullable();
            $table->integer('new_inventory_quantity')->nullable();
            $table->ipAddress('ip_address')->nullable();
            $table->timestamps();
        });

        Schema::create('boutique_blocked_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->references('id')->on('users');
            $table->foreignId('blocked_by_user_id')->references('id')->on('users');
            $table->timestamps();
        });

        Schema::create('boutique_blocked_value', function (Blueprint $table) {
            $table->id();
            $table->float('value', 16, 2);
            $table->timestamps();
        });

        Schema::create('boutique_inventory_ingresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('boutique_product_id')->references('id')->on('boutique_products');
            $table->foreignId('user_id')->references('id')->on('users');
            $table->string('A1');
            $table->string('A2');
            $table->string('A3');
            $table->string('A4');
            $table->string('A5');
            $table->string('A6');
            $table->string('A7');
            $table->string('B1');
            $table->string('B2');
            $table->string('B3');
            $table->string('B4');
            $table->string('B5');
            $table->string('B6');
            $table->string('B7');
            $table->string('B8');
            $table->string('B9');
            $table->string('B10');
            $table->string('B11');
            $table->string('B12');
            $table->string('B13');
            $table->string('B14')->default(0);
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
        Schema::dropIfExists('boutique_sales');
        Schema::dropIfExists('boutique_inventories');
        Schema::dropIfExists('boutique_products');
        Schema::dropIfExists('boutique_categories');
        Schema::dropIfExists('boutique_logs');
        Schema::dropIfExists('boutique_products_logs');
        Schema::dropIfExists('boutique_blocked_users');
        Schema::dropIfExists('boutique_blocked_value');
        Schema::dropIfExists('boutique_inventory_ingresses');
    }
}
