<?php

namespace App\Models\Attendance;

use Illuminate\Database\Eloquent\Model;

/**
 * @method static where(string $string, $id)
 * @method static create(array $array)
 */
class Attendance extends Model
{
    protected $guarded = ['id'];

    public function user()
    {
        return $this->hasMany(User::class);
    }

    public function statuses()
    {
        return $this->hasMany(AttendanceStatus::class, 'attendance_type');
    }

    public function comments()
    {
        return $this->hasMany(AttendanceComment::class, 'attendance_id');
    }
}
