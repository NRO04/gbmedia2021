<?php

namespace App\Models\Bookings;

use Illuminate\Database\Eloquent\Model;

/**
 * @method static where(string $string, $id)
 * @method static select(string $string, string $string1, string $string2)
 * @method static leftjoin(string $string, string $string1, string $string2)
 */
class BookingType extends Model
{
    protected $guarded = [];
    protected $table = "booking_types";
}
