<?php

namespace App\Models\Loans;

use Illuminate\Database\Eloquent\Model;
use App\Models\Loans\Loan;

class LoanInstallments extends Model
{
   	function loan(){
    	return $this->belongsTo(Loan::class);
    }
}
