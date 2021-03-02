<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSatelliteTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('satellite_payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->integer('has_retention');
            $table->integer('pay_with');
            $table->timestamps();
        });

        Schema::create('satellite_owners', function (Blueprint $table) {
            $table->id();
            $table->string('owner', 150);
            $table->string('first_name', 150)->nullable();
            $table->string('second_name', 150)->nullable();
            $table->string('last_name', 150)->nullable();
            $table->string('second_last_name', 150)->nullable();
            $table->string('document_number',50)->nullable();
            $table->string('email', 150);
            $table->string('phone', 50)->nullable();
            $table->string('others_emails', 250)->nullable();
            $table->string('statistics_emails', 250)->nullable();
            $table->foreignId('department_id')->nullable()->references('id')->on('global_departments');
            $table->foreignId('city_id')->nullable()->references('id')->on('global_cities');
            $table->string('address', 150)->nullable();
            $table->string('neighborhood', 150)->nullable();
            $table->integer('commission_percent')->default(90);
            $table->foreignId('payment_method')->default(1)->references('id')->on('satellite_payment_methods');
            $table->integer('simple_regime')->default(0);
            $table->foreignId('user_manager')->nullable()->references('id')->on('users');
            $table->integer('status')->default(1);
            $table->string('status_comment',250)->nullable();
            $table->integer('is_user')->default(0);
            $table->foreignId('user_id')->nullable()->references('id')->on('users');
            $table->string('purchase_limit', 150)->nullable();
            $table->timestamps();
        });

        Schema::create('satellite_owners_documentations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner')->references('id')->on('satellite_owners');
            $table->integer('type');
            $table->string('file', 150);
            $table->timestamps();
        });

        Schema::create('satellite_owners_payment_info', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner')->nullable()->references('id')->on('satellite_owners');
            $table->string('holder', 150)->nullable();
            $table->foreignId('bank')->nullable()->references('id')->on('global_banks');
            $table->string('bank_usa', 100)->nullable();
            $table->foreignId('document_type')->nullable()->references('id')->on('global_documents');
            $table->string('document_number', 150)->nullable();
            $table->integer('account_type')->default(0);
            $table->string('account_number', 150)->nullable();
            $table->string('city_id')->nullable();
            $table->string('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('country')->nullable();
            $table->timestamps();
        });

        Schema::create('satellite_owners_commission_relation', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_giver')->references('id')->on('satellite_owners');
            $table->foreignId('owner_receiver')->references('id')->on('satellite_owners');
            $table->integer('percent');
            $table->integer('type');
            $table->foreignId('page')->nullable()->references('id')->on('setting_pages');
            $table->timestamps();
        });

        Schema::create('satellite_users_documents_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->timestamps();
        });

        Schema::create('satellite_users', function (Blueprint $table) {
            $table->id();
            $table->string('first_name', 150);
            $table->string('second_name', 150)->nullable();
            $table->string('last_name', 150)->nullable();
            $table->string('second_last_name', 150)->nullable();
            $table->date('birth_date');
            $table->foreignId('document_type')->references('id')->on('satellite_users_documents_types');
            $table->string('document_number',50);
            $table->foreignId('country_id')->references('id')->on('global_countries');
            $table->foreignId('created_by')->references('id')->on('users');
            $table->foreignId('modified_by')->references('id')->on('users');
            $table->integer('status')->default(1);
            $table->timestamps();
        });

        Schema::create('satellite_users_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('satellite_user_id')->references('id')->on('satellite_users');
            $table->string('image', 150);
            $table->integer('type');
            $table->timestamps();
        });

        Schema::create('satellite_accounts_status', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('color', 150);
            $table->string('background', 150);
            $table->timestamps();
        });

        Schema::create('satellite_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->references('id')->on('satellite_owners');
            $table->foreignId('page_id')->references('id')->on('setting_pages');
            $table->foreignId('status_id')->references('id')->on('satellite_accounts_status');
            $table->string('nick', 150);
            $table->string('original_nick', 150);
            $table->string('first_name', 150)->nullable();
            $table->string('second_name', 150)->nullable();
            $table->string('last_name', 150)->nullable();
            $table->string('second_last_name', 150)->nullable();
            $table->date('birth_date');
            $table->string('access', 150)->nullable();
            $table->string('password', 150)->nullable();
            $table->string('live_id', 150)->nullable();
            $table->integer('from_gb')->default(0);
            $table->foreignId('user_id')->nullable()->constrained();
            $table->foreignId('satellite_user_id')->nullable()->references('id')->on('satellite_users');
            $table->string('comment',200)->nullable();
            $table->integer('email_sent')->default(0);
            $table->foreignId('modified_by')->references('id')->on('users');
            $table->timestamps();
        });

        Schema::create('satellite_accounts_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->references('id')->on('satellite_accounts');
            $table->string('type', 150);
            $table->string('action', 50);
            $table->string('previous', 150)->nullable();
            $table->string('now', 150)->nullable();
            $table->foreignId('created_by')->references('id')->on('users');
            $table->timestamps();
        });

        Schema::create('satellite_accounts_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->references('id')->on('satellite_accounts');
            $table->string('note', 255);
            $table->foreignId('created_by')->references('id')->on('users');
            $table->timestamps();
        });

        Schema::create('satellite_accounts_partners', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->references('id')->on('satellite_accounts');
            $table->string('name', 250);
            $table->timestamps();
        });

        Schema::create('satellite_templates_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->timestamps();
        });

        Schema::create('satellite_templates_pages_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_type_id')->references('id')->on('satellite_templates_types');
            $table->string('name', 150);
            $table->integer('nick')->default(0);
            $table->integer('full_name')->default(0);
            $table->integer('access')->default(0);
            $table->integer('password')->default(0);

            $table->timestamps();
        });

        Schema::create('satellite_templates_for_emails', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_page_id')->references('id')->on('satellite_templates_pages_fields');
            $table->string('subject', 150);
            $table->longText('body')->nullable();
            $table->foreignId('user_id')->nullable()->constrained();
            $table->timestamps();
        });

        Schema::create('satellite_payment_pages', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('type', 50);
            $table->string('cell_nick', 50);
            $table->string('cell_value', 50);
            $table->foreignId('setting_page_id')->references('id')->on('setting_pages');
            $table->integer('has_euro')->default(0);
            $table->string('description', 150)->nullable();
            $table->string('image', 150)->nullable();
            $table->timestamps();
        });

        Schema::create('satellite_payment_files', function (Blueprint $table) {
            $table->id();
            $table->date('payment_date');
            $table->date('start_date');
            $table->date('end_date');
            $table->foreignId('page_id')->references('id')->on('satellite_payment_pages');
            $table->string('file_url', 150);
            $table->string('trm', 50)->nullable();
            $table->string('euro', 50)->nullable();
            $table->foreignId('created_by')->references('id')->on('users');
            $table->timestamps();
        });

        Schema::create('satellite_payment_payroll', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->nullable()->references('id')->on('satellite_owners');
            $table->integer('is_user')->default(0);
            $table->date('payment_date');
            $table->string('payment_range', 150)->nullable();
            $table->string('total', 150)->nullable();
            $table->string('percent', 150)->nullable();
            $table->string('percent_studio', 150)->nullable();
            $table->string('percent_gb', 150)->nullable();
            $table->string('percent_gb_pesos', 150)->default(0);
            $table->string('trm', 150)->nullable();
            $table->string('transaction', 150)->nullable();
            $table->string('retention', 150)->nullable();
            $table->string('payment', 150)->nullable();
            $table->foreignId('payment_methods_id')->nullable()->references('id')->on('satellite_payment_methods');
            $table->string('holder', 150)->nullable();
            $table->foreignId('bank')->nullable()->references('id')->on('global_banks');
            $table->string('bank_usa', 100)->nullable();
            $table->foreignId('document_type')->nullable()->references('id')->on('global_documents');
            $table->string('document_number', 150)->nullable();
            $table->integer('account_type')->default(0);
            $table->string('account_number', 150)->nullable();
            $table->string('city_id')->nullable();
            $table->string('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('country')->nullable();
            $table->boolean('rut')->default(0);
            $table->string('first_time')->default(0);
            $table->foreignId('created_by')->references('id')->on('users');
            $table->timestamps();
        });

        Schema::create('satellite_payment_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->nullable()->references('id')->on('satellite_owners');
            $table->foreignId('page_id')->references('id')->on('setting_pages');
            $table->foreignId('account_id')->nullable()->references('id')->on('satellite_accounts');
            $table->foreignId('payroll_id')->nullable()->references('id')->on('satellite_payment_payroll');
            $table->foreignId('file_id')->references('id')->on('satellite_payment_files');
            $table->string('nick', 150)->nullable();
            $table->string('amount', 50)->nullable();
            $table->date('payment_date');
            $table->string('live_id', 150)->nullable();
            $table->text('description', 250)->nullable();
            $table->foreignId('created_by')->references('id')->on('users');
            $table->timestamps();
        });

        Schema::create('satellite_payment_commissions', function (Blueprint $table) {
            $table->id();
            $table->date('payment_date')->nullable();
            $table->foreignId('owner_id')->references('id')->on('satellite_owners');
            $table->foreignId('payroll_id')->nullable()->references('id')->on('satellite_payment_payroll');
            $table->integer('assign_to')->default(0);
            $table->string('amount', 50)->nullable();
            $table->text('description', 250)->nullable();
            $table->foreignId('coming_from')->nullable()->references('id')->on('satellite_owners');
            $table->foreignId('created_by')->references('id')->on('users');
            $table->timestamps();
        });

        //type 1 manuales, 2 cafeteria, 3 boutique
        Schema::create('satellite_payment_deductions', function (Blueprint $table) {
            $table->id();
            $table->date('payment_date')->nullable();
            $table->date('finished_date')->nullable();
            $table->foreignId('owner_id')->references('id')->on('satellite_owners');
            $table->integer('deduction_to')->default(0);
            $table->string('total', 150)->nullable();
            $table->string('times_paid', 50)->default(0);
            $table->string('amount', 150)->nullable();
            $table->date('last_pay')->nullable();
            $table->text('description', 250)->nullable();
            $table->integer('type')->default(1);
            $table->integer('type_foreign_id')->nullable();
            $table->foreignId('created_by')->references('id')->on('users');
            $table->boolean('status')->default(0);
            $table->timestamps();
        });

        Schema::create('satellite_payment_paydeductions', function (Blueprint $table) {
            $table->id();
            $table->date('payment_date');
            $table->foreignId('owner_id')->references('id')->on('satellite_owners');
            $table->foreignId('deduction_id')->references('id')->on('satellite_payment_deductions');
            $table->foreignId('payroll_id')->nullable()->references('id')->on('satellite_payment_payroll');
            $table->string('amount', 50)->nullable();
            $table->foreignId('created_by')->references('id')->on('users');
            $table->timestamps();
        });

        Schema::create('satellite_template_statistics', function (Blueprint $table) {
            $table->id();
            $table->string('subject',255)->nullable();
            $table->string('password',150)->nullable();
            $table->string('topic',155)->nullable();
            $table->text('header',255)->nullable();
            $table->string('title1',255)->nullable();
            $table->text('section1',255)->nullable();
            $table->string('title2',255)->nullable();
            $table->text('section2',255)->nullable();
            $table->string('title3',255)->nullable();
            $table->text('section3',255)->nullable();
            $table->string('color1',150)->nullable();
            $table->string('color2',150)->nullable();
            $table->string('studio',150)->nullable();
            $table->string('sign',150)->nullable();
            $table->string('url_web',150)->nullable();
            $table->string('phone',150)->nullable();
            $table->string('cell',150)->nullable();
            $table->string('skype',150)->nullable();
            $table->string('facebook',150)->nullable();
            $table->string('twitter',150)->nullable();
            $table->string('instagram',150)->nullable();
            $table->string('linkedin',150)->nullable();
            $table->string('pinterest',150)->nullable();
            $table->foreignId('modified_by')->nullable()->references('id')->on('users');
            $table->timestamps();
        });

//        Schema::create('satellite_contracts', function (Blueprint $table) {
//            $table->id();
//            $table->string('studio_name', 250);
//            $table->string('company_type', 100);
//            $table->string('holder', 150);
//            $table->string('card_id', 150);
//            $table->string('company', 250)->nullable();
//            $table->string('nit', 250)->nullable();
//            $table->string('address', 250);
//            $table->string('city', 150);
//            $table->string('department', 150);
//            $table->string('phone', 100);
//            $table->string('email', 150);
//            $table->string('percent', 150);
//            $table->string('payment_method', 150);
//            $table->string('clause', 150);
//            $table->string('years', 10);
//            $table->integer('increase')->default(1);
//            $table->foreignId('from')->references('id')->on('tenants');
//            $table->string('from_name', 150);
//
//            $table->timestamps();
//        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('satellite_payment_methods');
        Schema::dropIfExists('satellite_owners');
        Schema::dropIfExists('satellite_owners_documentations');
        Schema::dropIfExists('satellite_owners_payment_info');
        Schema::dropIfExists('satellite_owners_commission_relation');
        Schema::dropIfExists('satellite_users_documents_types');
        Schema::dropIfExists('satellite_users');
        Schema::dropIfExists('satellite_users_images');
        Schema::dropIfExists('satellite_accounts_status');
        Schema::dropIfExists('satellite_accounts');
        Schema::dropIfExists('satellite_accounts_logs');
        Schema::dropIfExists('satellite_accounts_notes');
        Schema::dropIfExists('satellite_accounts_partners');
        Schema::dropIfExists('satellite_templates_types');
        Schema::dropIfExists('satellite_templates_pages_fields');
        Schema::dropIfExists('satellite_templates_for_emails');
        Schema::dropIfExists('satellite_payment_pages');
        Schema::dropIfExists('satellite_payment_files');
        Schema::dropIfExists('satellite_payment_payroll');
        Schema::dropIfExists('satellite_payment_accounts');
        Schema::dropIfExists('satellite_payment_commissions');
        Schema::dropIfExists('satellite_payment_deductions');
        Schema::dropIfExists('satellite_payment_paydeductions');
        Schema::dropIfExists('satellite_contracts');
    }
}
