<?php

namespace App\Models\Settings;

use Illuminate\Database\Eloquent\Model;

class SettingPageTasks extends Model
{
    protected $table = "setting_page_tasks";

    public function typeOption()
    {
        return $this->belongsTo(SettingPageTaskTypes::class, 'page_task_type_id');
    }
}