<?php

namespace App\Models\Payrolls;

use Illuminate\Database\Eloquent\Model;
use App\User;

class Payroll extends Model
{
    protected $guarded = [];

    function user(){
    	return $this->belongsTo(User::class, 'user_id');
    }
}
