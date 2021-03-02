<?php

namespace App\Models\HumanResources;

use Illuminate\Database\Eloquent\Model;

class RHVacationRequest extends Model
{
    protected $primarykey = 'id';
    protected $table = 'rh_vacation_requests';
    protected $fillable = ['user_id',
                           'user_confirm_id',
                           'start_date',
                           'end_date',
                           'state',
                           'reason_deny',
                           'created_at',
                           'updated_at'];

    public function user_vacation()
    {
        return $this->belongsTo('App\User','user_id');
    }

    public function user_cofirm()
    {
        return $this->belongsTo('App\User','user_confirm_id');
    }


}
