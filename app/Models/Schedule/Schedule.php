<?php

namespace App\Models\Schedule;

use Illuminate\Database\Eloquent\Model;
use App\User;

/**
 * @method static findOrFail($id)
 * @method static where(string $string, $model_id)
 */
class Schedule extends Model
{
    protected $guarded = ['id'];

    public function user()
    {
    	return $this->belongsTo(User::class , 'user_id');
    }
}
