<?php

namespace App\Models\Satellite;

use App\Models\Statistics\Statistics;
use App\Models\Settings\SettingPage;
use Illuminate\Database\Eloquent\Model;
use App\User;

class SatelliteAccount extends Model
{
    protected $guarded = ['id'];
    
    public function owner(){
    	return $this->belongsTo(SatelliteOwner::class, 'owner_id');
    }

    public function page(){
    	return $this->belongsTo(SatelliteTemplatesPagesField::class, 'page_id');
    }

    public function page_account(){
        return $this->belongsTo(SettingPage::class, 'page_id');
    }

    public function partners(){
    	return $this->hasMany(SatelliteAccountPartner::class, 'account_id');
    }

    public function modified_by_user(){
    	return $this->belongsTo(User::class, 'modified_by');
    }

    public function status(){
    	return $this->belongsTo(SatelliteAccountStatus::class, 'status_id');
    }
}
