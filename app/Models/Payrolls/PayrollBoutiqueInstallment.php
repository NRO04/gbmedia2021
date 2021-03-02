<?php

namespace App\Models\Payrolls;

use Illuminate\Database\Eloquent\Model;
use App\User;

class PayrollBoutiqueInstallment extends Model
{
    protected $table = "payroll_boutique_installment";

    function created_by_user(){
    	return $this->belongsTo(User::class, 'created_by');
    }
}
