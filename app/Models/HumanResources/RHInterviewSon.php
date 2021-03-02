<?php

namespace App\Models\HumanResources;

use Illuminate\Database\Eloquent\Model;

class RHInterviewSon extends Model
{
    protected $primaryKey = 'id';
    protected $table = 'rh_interviewer_son';
    protected $fillable = [
        'rh_interview_id',
        'name'
    ];
}
