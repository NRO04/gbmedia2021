<?php

namespace App\Models\Satellite;

use Illuminate\Database\Eloquent\Model;
use App\Models\Settings\SettingPage;

class SatellitePaymentAccount extends Model
{
    protected $guarded = [];

    public function page(){
    	return $this->belongsTo(SettingPage::class, 'page_id');
    }

    public function owner(){
        return $this->belongsTo(SatelliteOwner::class, 'owner_id');
    }

    public function account(){
        return $this->belongsTo(SatelliteAccount::class, 'account_id');
    }
}
