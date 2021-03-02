<?php

namespace App\Models\Loans;

use Illuminate\Database\Eloquent\Model;
use App\User;

class Loan extends Model
{
    function user(){
    	return $this->belongsTo(User::class);
    }
}
