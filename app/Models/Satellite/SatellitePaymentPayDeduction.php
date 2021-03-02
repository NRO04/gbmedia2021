<?php

namespace App\Models\Satellite;

use Illuminate\Database\Eloquent\Model;
use App\User;

class SatellitePaymentPayDeduction extends Model
{
	protected $table = "satellite_payment_paydeductions";

    public function created_by_user()
    {
    	return $this->belongsTo(User::class, 'created_by');
    }
}
