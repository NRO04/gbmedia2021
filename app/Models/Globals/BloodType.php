<?php

namespace App\Models\Globals;

use Illuminate\Database\Eloquent\Model;

class BloodType extends Model
{
    protected $primaryKey = 'id';
    protected $table = 'global_blood_types';
    protected $fillable = [
        'name',
    ];
}
