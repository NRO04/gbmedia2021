<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContractsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->string('url');
            $table->string('image');
            $table->integer('position');
            $table->integer('active')->default(1);
            $table->timestamps();
        });

        Schema::create('role_has_contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->references('id')->on('contracts');
            $table->foreignId('setting_role_id')->references('id')->on('setting_roles');
            $table->timestamps();
        });

        Schema::create('contracts_module_info', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
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
        Schema::dropIfExists('contracts');
    }
}
