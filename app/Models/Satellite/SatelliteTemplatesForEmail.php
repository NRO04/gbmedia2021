<?php

namespace App\Models\Satellite;

use Illuminate\Database\Eloquent\Model;
use App\User;

class SatelliteTemplatesForEmail extends Model
{
	protected $guarded = [];

    public function modified_by_user(){
    	return $this->belongsTo(User::class, 'user_id');
    }

    public function fields()
    {
        return $this->belongsTo(SatelliteTemplatesPagesField::class, 'template_page_id');
    }
}
