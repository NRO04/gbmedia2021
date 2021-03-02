<?php

namespace App\Models\Contacts;

use Illuminate\Database\Eloquent\Model;

/**
 * @method static where(string $string, $from)
 * @method static create(array $array)
 */
class Contact extends Model
{
    protected $guarded = ['id'];
}
