<?php

namespace App\Models\Globals;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    protected $primaryKey = 'id';
    protected $table = 'global_departments';
    protected $fillable = [
        'name',
    ];

    public function DepartmentToCities()
    {
        return $this->hasMany('App\Models\Globals\City','department_id');
    }
}
