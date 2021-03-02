<?php

namespace App\Models\HumanResources;

use Illuminate\Database\Eloquent\Model;

class RHExtraValue extends Model
{
    protected $primaryKey = 'id';
    protected $table = 'rh_extra_values';
    protected $fillable = ['day_value','night_value','created_at','updated_at'];
}
