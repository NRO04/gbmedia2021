<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTenantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->bigIncrements('id');

            // your custom columns may go here
            $table->timestamps();
            $table->json('data')->nullable();
        });

        Schema::create('tenant_has_tenants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->references('id')->on('tenants');
            $table->foreignId('has_tenant_id')->references('id')->on('tenants');
            $table->timestamps();
        });

        Schema::create('user_has_tenants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('from_user_id')->references('id')->on('users');
            $table->foreignId('from_tenant_id')->references('id')->on('tenants');
            $table->foreignId('to_user_id')->references('id')->on('users');
            $table->foreignId('to_tenant_id')->references('id')->on('tenants');
            $table->timestamps();
        });

        Schema::create('tenant_has_contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->references('id')->on('tenants');
            $table->integer('active')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('tenants');
        Schema::dropIfExists('tenant_has_tenants');
        Schema::dropIfExists('user_has_tenants');
    }
}
