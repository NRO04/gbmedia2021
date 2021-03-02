<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            //foreign keys
            $table->foreignId('setting_location_id')->nullable()->constrained();
            $table->foreignId('setting_role_id')->references('id')->on('setting_roles');
            $table->foreignId('blood_type_id')->references('id')->on('global_blood_types');
            $table->foreignId('department_id')->references('id')->on('global_departments');
            $table->foreignId('city_id')->references('id')->on('global_cities');
            $table->foreignId('document_id')->references('id')->on('global_documents');
            $table->foreignId('contract_id')->default(1)->references('id')->on('global_type_contracts');
            $table->foreignId('eps_id')->default(1)->references('id')->on('global_eps');
            //personal information
            $table->string('first_name', 255);
            $table->string('middle_name', 255)->nullable();
            $table->string('last_name', 255);
            $table->string('second_last_name', 255)->nullable();
            $table->string('nick', 255)->nullable();
            $table->date('birth_date')->nullable();;
            $table->string('document_number')->nullable();
            $table->date('expiration_date')->nullable();
            $table->string('personal_email', 255)->nullable()->unique();
            $table->string('password', 255);
            $table->string('email', 255)->unique();
            $table->string('mobile_number')->nullable();
            $table->string('hangouts_password', 255)->nullable();
            $table->string('nationality', 5)->nullable();
            $table->string('address')->nullable();
            $table->string('neighborhood', 200)->nullable();

            $table->string('address_line_2', 200)->nullable();
            $table->string('emergency_contact', 150)->nullable();
            $table->string('emergency_phone', 150)->nullable();
            $table->integer('has_bank_account')->default(0);
            $table->integer('has_bank_without_retention')->default(0);
            $table->foreignId('bank_account_id')->default(1)->references('id')->on('global_banks');
            $table->foreignId('bank_account_document_id')->default(1)->references('id')->on('global_documents');
            $table->string('bank_account_owner')->nullable();
            $table->string('bank_account_document_number')->nullable();
            $table->string('bank_account_number')->nullable();
            $table->string('bank_account_type')->nullable();
            $table->string('bank_account_city')->nullable();
            $table->integer('current_salary')->default(0);
            $table->integer('starting_salary')->default(0);
            $table->date('admission_date')->nullable();
            $table->date('contract_date')->nullable();
            $table->boolean('has_social_security')->default(0);
            $table->string('social_security_amount', 100)->nullable();
            $table->boolean('status')->default(1);
            $table->text('description_retirement')->nullable();
            $table->date('date_retirement')->nullable();
            $table->string('user_key', 255)->nullable();
            $table->boolean('u_status_as')->default(0);
            $table->boolean('is_admin')->default(0);
            $table->string('user_passcode', 150)->nullable();
            $table->boolean('is_passcode_active')->default(0);
            $table->string('unique_code', 255)->nullable();
            $table->string('work_permit')->nullable();
            $table->string('theme', 150)->default('c-app c-dark-theme');
            $table->string('avatar', 150)->nullable();
            $table->boolean('has_uniform')->default(0);
            $table->string('blouse_size')->nullable();
            $table->string('pants_size')->nullable();
            $table->string('pants_long')->nullable();
            $table->boolean('has_bonus')->default(0);
            $table->string('bonus_amount')->nullable();
            $table->boolean('has_mobilization')->default(0);
            $table->string('mobilization_amount')->nullable();
            $table->boolean('has_transportation_aid')->default(0);
            $table->string('transportation_aid_amount')->nullable();
            $table->timestamp('last_seen')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('user_images', function (Blueprint $table){
            $table->id();
            $table->foreignId('user_id')->references('id')->on('users');
            $table->string('image', 200)->nullable();
            $table->timestamps();
        });

        Schema::create('user_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->references('id')->on('global_documents');
            $table->foreignId('user_id')->references('id')->on('users');
            $table->string('document_number', 100)->nullable();
            $table->date('expiration_date')->nullable();
            $table->string('file_name')->nullable();
            $table->enum('type', ['front_document', 'back_document', 'face_id_document', 'rut'])->nullable();
            $table->timestamps();
        });

        /*Schema::create('user_payment_methods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->references('id')->on('users');
            $table->string('account_holder');
            $table->foreignId('document_id')->references('id')->on('global_documents');
            $table->string('document_id_number');
            $table->foreignId('payment_method_id')->references('id')->on('payment_methods');
            $table->string('account_number');
            $table->string('account_type');
            $table->foreignId('country_id')->references('id')->on('global_countries');
            $table->string('city');
            $table->string('billing_address');
            $table->timestamps();
        });*/

        Schema::create('user_retirement_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->references('id')->on('users');
            $table->foreignId('created_by_user_id')->references('id')->on('users');
            $table->text('description');
            $table->date('starting_date')->nullable();
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
        Schema::dropIfExists('users');
        Schema::dropIfExists('user_documents');
        Schema::dropIfExists('user_has_contracts');
        Schema::dropIfExists('user_payment_methods');
        Schema::dropIfExists('user_images');
        Schema::dropIfExists('user_retirement_history');
    }
}
