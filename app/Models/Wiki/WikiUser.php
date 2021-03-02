<?php

namespace App\Models\Wiki;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

/**
 * @method static create(array $array)
 * @method static updateOrCreate(array $array)
 */
class WikiUser extends Model
{
    use Notifiable;

    protected $connection = "external";
    protected $table = "wiki_users";
    protected $guarded = ['id'];

    public function users()
    {
        $this->belongsToMany(User::class);
    }

    public function wiki()
    {
        $this->hasMany(Wiki::class);
    }

}
