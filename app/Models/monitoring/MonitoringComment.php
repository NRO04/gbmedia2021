<?php

namespace App\Models\Monitoring;

use Illuminate\Database\Eloquent\Model;

class MonitoringComment extends Model
{
    protected $guarded = ['id'];
    protected $table = "monitoring_comments";
}
