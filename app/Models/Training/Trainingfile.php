<?php

namespace App\Models\Training;

use Illuminate\Database\Eloquent\Model;

/**
 * @method static create(array $array)
 * @method static findOrFail($id)
 */
class Trainingfile extends Model
{
    protected $connection = "external";
    protected $table = "training_files";
    protected $guarded = ['id'];
}
