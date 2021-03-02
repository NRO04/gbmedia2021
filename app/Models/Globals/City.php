<?php

namespace App\Models\Globals;

use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    protected $primaryKey = 'id';
    protected $table = 'global_cities';
    protected $fillable = [
        'department_id',
        'name',
        'status',
    ];

    public function CityToDepartment()
    {
        return $this->belongsTo('App\Models\Globals\Department','department_id');
    }

}
