<?php

namespace App\Models\Payrolls;

use Illuminate\Database\Eloquent\Model;
use App\User;

class PayrollBoutique extends Model
{
    protected $table = "payroll_boutique";
    function user(){
    	return $this->belongsTo(User::class, 'user_id');
    }

    function instalemnts() {
    	return $this->hasMany(PayrollBoutiqueInstallment::class, 'payroll_boutique_id');
    }
}
