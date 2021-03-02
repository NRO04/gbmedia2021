<?php

namespace App\Models\Training;

use Illuminate\Database\Eloquent\Model;

class TrainingUsers extends Model
{
    protected $connection = "external";
    protected $table = "training_users";
    protected $guarded = ['id'];
}
