<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RhHumanResource extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rh_interviews', function (Blueprint $table) {
            $table->id();
            //foreign keys
            $table->foreignId('user_interviewer_id')->references('id')->on('users');
            $table->foreignId('user_id')->nullable()->constrained();
            $table->foreignId('setting_role_id')->constrained();
            $table->foreignId('document_id')->references('id')->on('global_documents'); 
            $table->foreignId('blood_type_id')->references('id')->on('global_blood_types'); 
            $table->foreignId('department_id')->references('id')->on('global_departments'); 
            $table->foreignId('city_id')->references('id')->on('global_cities'); 
            //personal information
            $table->string('first_name')->nullable();
            $table->string('middle_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('second_last_name')->nullable();
            $table->date('birth_date')->nullable();
            $table->string('document_number')->nullable();
            $table->date('expiration_date')->nullable();
            $table->string('email');
            $table->string('mobile_number')->nullable();
            $table->string('address')->nullable();
            $table->string('neighborhood')->nullable();
            $table->string('lives_with')->nullable();
            $table->string('emergency_contact')->nullable();
            $table->string('emergency_phone')->nullable();
            $table->boolean('he_has_children')->default(false);
            $table->enum('availability', ['morning', 'afternoon', 'night', 'anytime']);
            //model web cam information
            $table->boolean('was_model')->nullable();
            $table->string('which_study')->nullable();
            $table->string('how_long')->nullable();
            $table->string('work_pages')->nullable();
            $table->string('how_much')->nullable();
            $table->string('retirement_reason')->nullable();
            //education information
            $table->enum('edu_level', ['primaria', 'bachillerato', 'carrera tecnica', 'universidad', 'postgrado'])->nullable();
            $table->string('edu_final')->nullable();
            $table->string('edu_name_inst')->nullable();
            $table->string('edu_city')->nullable();
            $table->string('edu_title')->nullable();
            $table->boolean('edu_validate')->nullable();
            $table->string('edu_type_study')->nullable();
            $table->string('edu_time_final')->nullable();
            $table->string('edu_name_inst_current')->nullable();
            $table->string('edu_schedule')->nullable();
            $table->string('edu_others')->nullable();
            //working Information
            $table->string('person_charge')->nullable();
            $table->integer('count_person')->default(0);
            $table->string('unemployment_time')->nullable();
            $table->text('developed_activities')->nullable();
            //Additional information
            $table->text('know_business')->nullable();
            $table->string('meet_us')->nullable();
            $table->string('recommended_name')->nullable();
            $table->text('strengths')->nullable();
            $table->text('personality')->nullable();
            $table->text('visualize')->nullable();
            $table->text('health_state')->nullable();
            $table->string('wage_aspiration')->nullable();
            $table->text('observations')->nullable();
            $table->boolean('it_adapts')->nullable();
            $table->string('not_adapts_reason')->nullable();
            $table->boolean('is_user')->default(false);
            /*$table->integer('id_user')->nullable();*/
            $table->boolean('cite')->nullable();
            $table->timestamps();
        });

        Schema::create('rh_interview_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rh_interview_id')->references('id')->on('rh_interviews');
            $table->foreignId('user_id')->constrained();
            $table->string('field')->nullable();
            $table->string('previous_value')->nullable();
            $table->string('new_value')->nullable();
            $table->timestamps();
        });

        Schema::create('rh_extra_values', function (Blueprint $table) {
            $table->id();
            $table->integer('day_value')->default(0);
            $table->integer('night_value')->default(0);
            $table->integer('day_percent')->default(0);
            $table->integer('night_percent')->default(0);
            $table->integer('day_sunday_percent')->default(0);
            $table->integer('night_sunday_percent')->default(0);
            $table->integer('transportation_aid')->default(0);
            $table->timestamps();
        });

        Schema::create('rh_interviewer_son', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rh_interview_id')->references('id')->on('rh_interviews');
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('rh_working_info', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rh_interview_id')->references('id')->on('rh_interviews');
            $table->string('name_bussines')->nullable();
            $table->string('time_worked')->nullable();
            $table->string('position')->nullable();
            $table->string('reason_withdrawal')->nullable();
            $table->timestamps();
        });

        Schema::create('rh_interviewer_img', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rh_interview_id')->references('id')->on('rh_interviews');
            $table->string('face')->nullable();
            $table->string('front')->nullable();
            $table->string('side')->nullable();
            $table->string('back')->nullable();
            $table->timestamps();
        });

        Schema::create('rh_extra_state', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('rh_extra_hours', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('user_acep_den_id')->nullable()->references('id')->on('users');
            $table->foreignId('state_id')->nullable()->references('id')->on('rh_extra_state');

            $table->string('start_time')->nullable();
            $table->string('end_time')->nullable();

            $table->string('extra_reason')->nullable();

            $table->string('daytime_hours')->nullable();
            $table->string('daytime_minutes')->nullable();
            $table->string('daytime_total')->nullable();

            $table->string('night_hours')->nullable();
            $table->string('night_minutes')->nullable();
            $table->string('night_total')->nullable();

            $table->string('total_extras')->nullable();
            $table->string('total')->nullable();

            $table->string('comment_denied')->nullable();
            $table->date('application_date')->nullable();
            $table->date('review_date')->nullable();
            $table->string('range')->nullable();
            $table->string('range_revision')->nullable();
            $table->string('day')->nullable();
            $table->string('month')->nullable();
            $table->string('year')->nullable();
            $table->timestamps();
        });

        Schema::create('rh_alarms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->integer('rha_interviews')->default(0);
            $table->integer('rha_extra_assign')->length(1)->default(0);
            $table->integer('rha_extra_request')->length(1)->default(0);
            $table->integer('rha_sol_vac')->length(1)->default(0);
            $table->integer('rha_annotate_vac')->length(1)->default(0);
            $table->timestamps();
        });

        Schema::create('rh_vacation_status', function (Blueprint $table) {
            $table->id();
            $table->string('status');
            $table->timestamps();
        });

        Schema::create('rh_vacation_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('user_confirm_id')->nullable()->references('id')->on('users');
            $table->foreignId('rh_vacation_status_id')->nullable()->references('id')->on('rh_vacation_status');
            $table->date('start_date');
            $table->date('end_date');
            $table->string('reason_deny')->nullable();
            $table->timestamps();
        });

        Schema::create('rh_vacation_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('user_confirm_id')->nullable()->references('id')->on('users');
            $table->foreignId('setting_role_id')->constrained();
            $table->string('rank');
            $table->date('date');
            $table->string('day');
            $table->string('month');
            $table->string('year');
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
        Schema::dropIfExists('rh_interviews');
        Schema::dropIfExists('rh_extra_values');
        Schema::dropIfExists('rh_extra_hours');
        Schema::dropIfExists('rh_alarms');
        Schema::dropIfExists('rh_vacation_requests');
        Schema::dropIfExists('rh_vacation_user');
    }
}
