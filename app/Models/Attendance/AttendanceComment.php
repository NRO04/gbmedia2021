<?php

namespace App\Models\Attendance;

use Illuminate\Database\Eloquent\Model;

/**
 * @method static create(array $array)
 */
class AttendanceComment extends Model
{
   protected $guarded = ['id'];
   protected $table = "attendance_comments";
}
