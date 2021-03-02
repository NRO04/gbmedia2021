<?php

namespace App\Models\Training;

use Illuminate\Database\Eloquent\Model;

/**
 * @method static where(array $array, array $array1)
 * @method static create(array $array)
 * @method static join(string $string, string $string1, string $string2, string $string3)
 * @method static firstOrCreate(array $array)
 */
class Completed extends Model
{
    protected $connection = "external";
    protected $guarded = ['id'];
    protected $table = "training_completed";


    public function trainings()
    {
        return $this->hasMany(Training::class);
    }

}
