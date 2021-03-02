<?php

namespace App\Models\Attendance;

use App\User;
use Illuminate\Database\Eloquent\Model;

/**
 * @method static select(string $string, string $string1, string $string2)
 * @method static join(string $string, string $string1, string $string2, string $string3)
 * @method static where(array[] $array)
 * @method static findOrFail()
 */
class AttendanceSummary extends Model
{
    protected $guarded = ['id'];
    protected $table = "attendance_summary";

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
