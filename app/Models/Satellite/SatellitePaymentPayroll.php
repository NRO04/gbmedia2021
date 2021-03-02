<?php

namespace App\Models\Satellite;

use Illuminate\Database\Eloquent\Model;
use App\Models\Globals\Bank;
use App\Models\Globals\Document;
use App\Models\Satellite\SatellitePaymentMethod;
use App\Models\Satellite\SatelliteOwner;

class SatellitePaymentPayroll extends Model
{
    protected $table = 'satellite_payment_payroll';
    protected $guarded = ['id'];

    public function globalBank()
    {
    	return $this->belongsTo(Bank::class, 'bank');
    }

    public function globalDocument()
    {
    	return $this->belongsTo(Document::class, 'document_type');
    }

    public function paymentMethods()
    {
    	return $this->belongsTo(SatellitePaymentMethod::class, 'payment_methods_id');
    }

    public function owner()
    {
    	return $this->belongsTo(SatelliteOwner::class, 'owner_id');
    }
}
