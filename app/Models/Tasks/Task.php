<?php

namespace App\Models\Tasks;

use Illuminate\Database\Eloquent\Model;
use App\Models\Settings\SettingRole;
use App\Models\Tasks\TaskUserStatus;
use App\User;

/**
 * @method static create(array $array)
 */
class Task extends Model
{
    protected $guarded = ['id'];
    
    function taskStatus(){
    	return $this->hasMany(TaskUserStatus::class);
    }

    function taskComments(){
        return $this->hasMany(TaskComment::class);
    }
}
