<?php

namespace App\Models\Training;

use App\User;
use Illuminate\Database\Eloquent\Model;

/**
 * @method static create(array $array)
 * @method static inRandomOrder()
 * @method static where(string $string, $id)
 */
class Question extends Model
{
    protected $connection = "external";
    protected $table = "training_questions";
    protected $guarded = ['id'];

    public function training()
    {
        return $this->belongsTo(Training::class);
    }

    public function answers()
    {
        return $this->hasMany(Answer::class, 'training_question_id');
    }
}
