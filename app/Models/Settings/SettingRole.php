<?php

namespace App\Models\Settings;

use Illuminate\Database\Eloquent\Model;

/**
 * @method static select(string $string)
 */
class SettingRole extends Model
{
    protected $primaryKey = 'id';
    protected $table = 'setting_roles';
    protected $fillable = [
        'name',
        'alternative_name',
        'task',
        'position',
        'is_admin',
    ];
}
