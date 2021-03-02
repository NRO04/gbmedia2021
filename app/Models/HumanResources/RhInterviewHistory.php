<?php

namespace App\Models\HumanResources;

use Illuminate\Database\Eloquent\Model;

class RhInterviewHistory extends Model
{
    protected $primaryKey = 'id';
    protected $table = 'rh_interview_history';
    protected $fillable = ['rh_interview_id','user_id','previous_value','new_value'];


    public function RhHistoryToRhInterview()
    {
        return $this->belongsTo('App\Models\HumanResources\RHInterviews','rh_interview_id');
    }

    public function RhHistoryToUser()
    {
        return $this->belongsTo('App\User','user_id');
    }
}


