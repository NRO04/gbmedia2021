<?php

namespace App\Models\Bookings;

use Illuminate\Database\Eloquent\Model;

/**
 * @method static create(array $array)
 * @method static select(string $string, string $string1, string $string2, string $string3)
 * @method static where(string $string, $id)
 * @method static join(string $string, string $string1, string $string2)
 * @method static leftjoin(string $string, string $string1, string $string2)
 * @method static findOrFail($schedule_type_id)
 */
class BookingSchedule extends Model
{
    public $guarded = ['id'];
    
    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
}
