<?php

namespace App\Models\Training;

use Illuminate\Database\Eloquent\Model;

/**
 * @method static create(array $array)
 * @method static where(array[] $array)
 */
class Answer extends Model
{
    protected $connection = "external";
    protected $table = "training_options";
    protected $guarded = ['id'];
    
    public function questions()
    {
        return $this->belongsTo(Question::class, 'training_option_id');
    }
}
