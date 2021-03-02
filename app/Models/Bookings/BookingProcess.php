<?php

namespace App\Models\Bookings;

use Illuminate\Database\Eloquent\Model;

/**
 * @method static create(array $array)
 * @method static where(string $string, $id)
 * @method static join(string $string, string $string1, string $string2, string $string3)
 * @method static whereDateBetween(string $string, string $toDateString, string $toDateString1)
 * @method static whereBetween(string $string, string $lastTenDays, string $currentDate)
 * @method static findOrFail($id)
 * @method static whereIn(string $string, int[] $array)
 */
class BookingProcess extends Model
{
    protected $guarded = ['id'];
    protected $table = "booking_processes";

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }


}
