<?php

namespace App\Models\Bookings;

use Illuminate\Database\Eloquent\Model;

/**
 * @method static create(array $array)
 * @method static where(string $string, $id)
 * @method static findOrFail($id)
 */
class Exonerate extends Model
{
    protected $table = "booking_exonerates";
    protected $guarded = [];
}
