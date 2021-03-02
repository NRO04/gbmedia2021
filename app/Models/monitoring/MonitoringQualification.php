<?php

namespace App\Models\monitoring;

use Illuminate\Database\Eloquent\Model;

class MonitoringQualification extends Model
{
    protected $guarded = ['id'];

    public function images()
    {
        return $this->hasMany(MonitoringImages::class);
    }
}
