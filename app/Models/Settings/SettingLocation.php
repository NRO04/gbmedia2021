<?php

namespace App\Models\Settings;

use App\User;
use Illuminate\Database\Eloquent\Model;

/**
 * @method static where(string $string, string $string1, string $string2)
 * @method static select(string $string, string $string1)
 */
class SettingLocation extends Model
{
    protected $fillable = [
        'name',
        'rooms',
        'base',
        'position',
        'address'
    ];

    function LocationRelations()
    	{
    		return $this->hasMany(SettingLocationPermission::class);
    	}

    function users()
        {
            return $this->hasMany(User::class);
        }
}
