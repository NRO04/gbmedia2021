<?php

namespace App\Models\HumanResources;

use Illuminate\Database\Eloquent\Model;

class RHAlarm extends Model
{
    protected $primaryKey = 'id';
    protected $table = 'rh_alarms';
    protected $fillable = ['user_id',
                           'rha_interviews',
                           'rha_extra_assign',
                           'rha_extra_request',
                           'rha_sol_vac',
                           'rha_annotate_vac',
                           'created_at',
                           'updated_at'];

    public function user_alarm()
    {
        return $this->belongsTo('App\User','user_id');
    }
}
