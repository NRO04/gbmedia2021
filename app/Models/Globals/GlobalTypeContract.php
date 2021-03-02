<?php

namespace App\Models\Globals;

use Illuminate\Database\Eloquent\Model;

class GlobalTypeContract extends Model
{
    protected $primaryKey = 'id';
    protected $table = 'global_type_contracts';
    protected $fillable = [
        'name',
    ];
}
