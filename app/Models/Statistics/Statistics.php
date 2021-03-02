<?php

namespace App\Models\Statistics;

use App\Models\Settings\SettingPage;
use App\User;
use Illuminate\Database\Eloquent\Model;

class Statistics extends Model
{
    protected $guarded = ['id'];

    public function pages()
    {
        return $this->hasMany(SettingPage::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
