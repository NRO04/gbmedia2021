<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\User;
use Carbon\Carbon;
use Faker\Generator as Faker;
use Illuminate\Support\Str;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
*/

function getModel(){
    $user = User::select('id')->where('setting_role_id', 14)->where('id', rand(1, 150) )->first();
    if ($user != null)
    {
        $id = $user->id;
        return $id;
    }
    else{
        getModel();
    }
}

$factory->define(\App\Models\Attendance\AttendanceSummary::class, function (Faker $faker) {

    $week_start = Carbon::now()->startOfWeek(Carbon::SUNDAY)->toDateString();
    $week_end = Carbon::now()->endOfWeek(Carbon::SATURDAY)->toDateString();
    $model_id = getModel();

    $users = User::select('id')->where('setting_role_id', 14)->get();
    dd($users);
    foreach ($users as $user){
        return [
            'model_id' => $user->id,
            'range' => $week_start."-".$week_end,
            'worked_days' => $faker->numberBetween($min = 1, $max = 7),
            'unjustified_days' => $faker->numberBetween($min = 1, $max = 4),
            'justified_days' => $faker->numberBetween($min = 1, $max = 4),
            'period' => $faker->numberBetween($min = 1, $max = 4),
            'total_minutes' => $faker->numberBetween($min = 60, $max = 480),
            'total_recovery_minutes' => $faker->numberBetween($min = 60, $max = 480),
            'goal' => $faker->numberBetween($min = 50, $max = 100),
            'created_by' => 1,
        ];
    }

});
