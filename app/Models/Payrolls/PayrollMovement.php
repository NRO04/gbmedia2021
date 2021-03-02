<?php

namespace App\Models\Payrolls;

use Illuminate\Database\Eloquent\Model;
use App\User;

class PayrollMovement extends Model
{
	protected $fillable = ['user_id', 'payroll_type_id', 'amount', 'created_by', 'comment', 'for_date'];

    function user(){
    	return $this->belongsTo(User::class);
    }

    function created_by_user(){
    	return $this->belongsTo(User::class, 'created_by');
    }
}
