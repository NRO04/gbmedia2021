<?php

namespace App\Models\monitoring;

use Illuminate\Database\Eloquent\Model;

class Monitoring extends Model
{
    protected $guarded = ['id'];
    protected $table = "monitoring";

    public function archives()
    {
        return $this->hasMany(MonitoringArchives::class);
    }

}
