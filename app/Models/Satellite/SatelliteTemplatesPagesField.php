<?php

namespace App\Models\Satellite;

use Illuminate\Database\Eloquent\Model;
use App\Models\Satellite\SatelliteTemplatesType;
use App\Models\Satellite\SatelliteTemplatesForEmail;

class SatelliteTemplatesPagesField extends Model
{
    function pageType(){
    	return $this->hasOne(SatelliteTemplatesType::class, 'template_type_id');
    }

    public function template_page(){
    	return $this->hasMany(SatelliteTemplatesForEmail::class, 'template_page_id');
    }

}
