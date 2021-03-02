<?php

namespace App\Models\Attendance;

use Illuminate\Database\Eloquent\Model;

class AttendanceStatus extends Model
{
    protected $guarded = ['id'];
    protected $table = "attendance_statuses";
}
