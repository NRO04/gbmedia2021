<?php

namespace App\Models\HumanResources;

use Illuminate\Database\Eloquent\Model;

class RHWorkingInfo extends Model
{
    protected $primaryKey = 'id';
    protected $table = 'rh_working_info';
    protected $fillable = [
        'rh_interview_id',
        'name_bussines',
        'time_worked',
        'position',
        'reason_withdrawal'
    ];
}
