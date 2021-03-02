<?php

namespace App\Models\HumanResources;

use Illuminate\Database\Eloquent\Model;

class RHVacationUser extends Model
{
    protected $primarykey = 'id';
    protected $table = 'rh_vacation_user';
    protected $fillable = ['user_id',
                           'setting_role_id',
                           'rank',
                           'date',
                           'day',
                           'month',
                           'year'];

    public function rhVactionUserToUser()
    {
        return $this->belongsTo('App\User','user_id');
    }
}
