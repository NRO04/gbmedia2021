<?php

namespace App\Models\Tasks;

use Illuminate\Database\Eloquent\Model;
use App\Models\Tasks\Task;
use App\User;

class TaskUserStatus extends Model
{
    protected $table = 'task_user_status';
    protected $fillable = ['task_id', 'user_id', 'status', 'pulsing', 'folder'];

    function task(){
    	return $this->belongsTo(Task::class);
    }

    function user(){
    	return $this->belongsTo(User::class);
    }
}
