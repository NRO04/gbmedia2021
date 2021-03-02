<?php

namespace App\Models\Satellite;

use Illuminate\Database\Eloquent\Model;

class SatellitePaymentDeduction extends Model
{
    protected $table = 'satellite_payment_deductions';

    public function owner(){
        return $this->belongsTo(SatelliteOwner::class, 'owner_id');
    }
}
