<?php

namespace App\Models\Satellite;

use Illuminate\Database\Eloquent\Model;
use App\Models\Satellite\SatelliteOwner;
use App\Models\Settings\SettingPage;

class SatelliteOwnerCommissionRelation extends Model
{
    protected $table = 'satellite_owners_commission_relation';

    public function ownerReceiver(){
    	return $this->belongsTo(SatelliteOwner::class, 'owner_receiver');
    }

    public function ownerGiver(){
    	return $this->belongsTo(SatelliteOwner::class, 'owner_giver');
    }
    

    public function settingPage(){
    	return $this->belongsTo(SettingPage::class, 'page');
    }
}
