<?php

namespace App\Models\Bookings;

use App\User;
use Illuminate\Database\Eloquent\Model;

/**
 * @method static create(array $array)
 * @method static join(string $string, string $string1, string $string2, string $string3)
 * @method static findOrFail($id)
 * @method static where(string $string, string $date_range)
 * @method static leftjoin(string $string, string $string1, string $string2)
 * @method static select(string $string)
 */
class Booking extends Model
{
    protected $guarded = ['id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function processes()
    {
        return $this->hasMany(BookingProcess::class);
    }

    public function schedules()
    {
        return $this->hasMany(BookingSchedule::class);
    }

    public function category()
    {
        return $this->belongsTo(BookingType::class);
    }
    
}
