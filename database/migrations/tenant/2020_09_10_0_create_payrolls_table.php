<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePayrollsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payrolls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->integer('month');
            $table->integer('year');
            $table->integer('salary1');
            $table->integer('worked_days1');
            $table->integer('salary2');
            $table->integer('worked_days2');
            $table->timestamps();
        });

        Schema::create('payroll_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50);
            $table->timestamps();
        });

        Schema::create('payroll_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('payroll_type_id')->constrained();
            $table->integer('amount');
            $table->foreignId('created_by')->references('id')->on('users');
            $table->string('comment', 255);
            $table->dateTime('for_date');
            $table->boolean('automatic')->default(0);
            $table->timestamps();
        });

        Schema::create('payroll_increases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->integer('amount');
            $table->timestamps();
        });

        Schema::create('payroll_boutique', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('boutique_sale_id')->references('id')->on('boutique_sales');
            $table->integer('amount');
            $table->integer('installment');
            $table->integer('status');
            $table->string('comment', 255);
            $table->timestamps();
        });

        Schema::create('payroll_boutique_installment', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_boutique_id')->references('id')->on('payroll_boutique');
            $table->integer('installment');
            $table->foreignId('created_by')->references('id')->on('users');
            $table->timestamps();
        });

        Schema::create('payroll_consecutive', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_boutique_id')->references('id')->on('payroll_boutique');
            $table->integer('installment');
            $table->foreignId('created_by')->references('id')->on('users');
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
        Schema::dropIfExists('payrolls');
        Schema::dropIfExists('payroll_types');
        Schema::dropIfExists('payroll_movements');
        Schema::dropIfExists('payroll_increases');
        Schema::dropIfExists('payroll_boutique');
        Schema::dropIfExists('payroll_boutique_installment');
    }
}
