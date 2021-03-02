<?php

namespace App\Models\Training;

use Illuminate\Database\Eloquent\Model;

/**
 * @method static create(array $array)
 * @method static where(string $string, $id)
 */
class Trainingroles extends Model
{
    protected $connection = "external";
    protected $table = "training_roles";
    protected $guarded = ['id'];
}
