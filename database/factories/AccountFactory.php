<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Satellite\SatelliteAccount;
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

$factory->define(SatelliteAccount::class, function (Faker $faker) {

    $nick = $faker->firstName."".$faker->lastName;
    return [
        'owner_id' => rand(1, 40),
        'page_id' => rand(1, 18),
        'status_id' => rand(1, 8),
        'nick' => $nick,
        'original_nick' => $nick,
        'first_name' => $faker->firstName,
        'second_name' => $faker->optional(0.3, "")->lastName,
        'last_name' => $faker->lastName,
        'second_last_name' => $faker->lastName,
        'birth_date' => $faker->dateTimeBetween($startDate = '-40 years', $endDate = '2002-07-15'),
        'access' => $faker->unique()->safeEmail,
        'password' => rand(1120405301, 1620405301),
        'live_id' => $faker->optional(0.3, "")->safeEmail.", ".$faker->optional(0.3, "")->safeEmail,
        'from_gb' => 0,
        'email_sent' => 0,
        'modified_by' => 3,
    ];

});
