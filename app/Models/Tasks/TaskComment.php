<?php

namespace App\Models\Tasks;

use App\User;
use App\Models\Tasks\Task;
use Illuminate\Database\Eloquent\Model;

/**
 * @method static create(array $array)
 */
class TaskComment extends Model
{
    protected $guarded = ['id'];
    
    function user(){
    	return $this->belongsTo(User::class);
    }

    function task(){
    	return $this->belongsTo(Task::class);
    }
}
