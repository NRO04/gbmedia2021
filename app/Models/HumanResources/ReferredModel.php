<?php

namespace App\Models\HumanResources;

use App\Models\Globals\City;
use App\Models\Globals\Department;
use App\Models\Tenancy\Tenant;
use Illuminate\Database\Eloquent\Model;

class ReferredModel extends Model
{
    protected $guarded = [];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class, 'studio_creator_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function images()
    {
        return $this->hasMany(ReferredModelImage::class);
    }
}
