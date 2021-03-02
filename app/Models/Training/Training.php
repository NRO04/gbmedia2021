<?php

namespace App\Models\Training;

use App\Models\Settings\SettingRole;
use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

/**
 * @method static create(array $array)
 * @method static find($id)
 * @method static findOrFail($id)
 */
class Training extends Model
{
    protected $connection = "external";
    protected $table = "trainings";
    protected $guarded = ["id"];
    protected $appends = ['video_url', 'image_url'];

    public function getVideoUrlAttribute()
    {
        return url(Storage::url('GB/trainings/videos/'.$this->video));
    }

    public function getImageUrlAttribute()
    {
        return url(Storage::url('GB/trainings/images/'.$this->cover));
    }

    public function questions()
    {
        return $this->hasMany(Question::class, 'training_question_id');
    }

    public function files()
    {
        return $this->hasMany(Trainingfile::class, 'training_file_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function roles()
    {
        return $this->hasMany(SettingRole::class);
    }
}
