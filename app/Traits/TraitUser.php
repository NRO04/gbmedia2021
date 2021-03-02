<?php

namespace App\Traits;

use App\User;

trait TraitUser
{
    public function modelsLocationTrait($setting_location_id)
    {
        $models = User::where('setting_role_id' , '=' , 14)
                      ->where('setting_location_id' , '=' , $setting_location_id)->where('status', 1)->get();
        return $models;
    }

}
