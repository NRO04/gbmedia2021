<?php

namespace App\Models\Training;

use Illuminate\Database\Eloquent\Model;

class TrainingStudios extends Model
{
    protected $connection = "external";
    protected $table = "training_studios";
    protected $guarded = ['id'];
}
