<?php

namespace App\Models\Bookings;

use Illuminate\Database\Eloquent\Model;

/**
 * @method static create(array $array)
 * @method static where(array[] $array)
 * @method static findOrFail($id)
 * @method static join(string $string, string $string1, string $string2)
 */
class QuarterDay extends Model
{
    protected $guarded = [];
    protected $table = "booking_quarters";
}
