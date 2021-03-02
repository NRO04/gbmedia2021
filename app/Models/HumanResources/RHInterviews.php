<?php

namespace App\Models\HumanResources;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RHInterviews extends Model
{
    protected $primaryKey = 'id';
    protected $table = 'rh_interviews';
    protected $fillable = [
        'user_interviewer_id',
        'user_id',
        'setting_role_id',
        'document_id',
        'blood_type_id',
        'department_id',
        'city_id',
        'first_name',
        'middle_name',
        'last_name',
        'second_last_name',
        'birth_date',
        'document_number',
        'expiration_date',
        'email',
        'mobile_number',
        'address',
        'neighborhood',
        'lives_with',
        'emergency_contact',
        'emergency_phone',
        'he_has_children',
        'availability',
        'was_model',
        'which_study',
        'how_long',
        'work_pages',
        'how_much',
        'retirement_reason',
        'edu_level',
        'edu_final',
        'edu_name_inst',
        'edu_city',
        'edu_title',
        'edu_validate',
        'edu_type_study',
        'edu_time_final',
        'edu_name_inst_current',
        'edu_schedule',
        'edu_others',
        'person_charge',
        'count_person',
        'unemployment_time',
        'developed_activities',
        'know_business',
        'meet_us',
        'recommended_name',
        'strengths',
        'personality',
        'visualize',
        'health_state',
        'wage_aspiration',
        'observations',
        'it_adapts',
        'not_adapts_reason',
        'cite'
    ];

    public function RHInterviewToRole()
    {
        return $this->belongsTo('App\Models\Settings\SettingRole','setting_role_id');
    }

    public function RHInterviewToImg()
    {
        return $this->hasOne('App\Models\HumanResources\RHInterviewImg','rh_interview_id');
    }

    public function RHInterviewToSon()
    {
        return $this->hasMany('App\Models\HumanResources\RHInterviewSon','rh_interview_id');
    }

    public function RHInterviewToWorking()
    {
        return $this->hasMany('App\Models\HumanResources\RHWorkingInfo','rh_interview_id');
    }

    public function RHInterviewToUser()
    {
        return $this->belongsTo('App\User','user_interviewer_id');
    }

    public function RHInterviewToCity()
    {
        return $this->belongsTo('App\Models\Globals\City','city_id');
    }

    public function RHInterviewToDocument()
    {
        return $this->belongsTo('App\Models\Globals\Document','document_id');
    }

    public function RHInterviewToBloodType()
    {
        return $this->belongsTo('App\Models\Globals\BloodType','blood_type_id');
    }

    public function InterviewUserShortName()
    {
        $name = $this->first_name." ".$this->last_name;

        return $name;
    }

}
