<?php

namespace App\Models\Globals;

use Illuminate\Database\Eloquent\Model;

/**
 * @method static firstOrCreate(array $array)
 */
class GlobalStatus extends Model
{
    protected $guarded = ['id'];
    protected $table = "global_statuses";
}
