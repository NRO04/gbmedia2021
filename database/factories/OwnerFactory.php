<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Satellite\SatelliteOwner;
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

$factory->define(SatelliteOwner::class, function (Faker $faker) {

    return [
        'owner' => $faker->firstName.$faker->lastName,
        'first_name' => $faker->firstName,
        'second_name' => $faker->optional(0.3, "")->lastName,
        'last_name' => $faker->lastName,
        'second_last_name' => $faker->lastName,
        'document_number' => rand(1120405301, 1520405301),
        'email' => $faker->unique()->safeEmail,
        'phone' => rand(1120405301, 1620405301),
        'others_emails' => $faker->optional(0.3, "")->safeEmail.", ".$faker->optional(0.3, "")->safeEmail,
        'department_id' => 24,
        'city_id' => 150,
        'address' => $faker->address,
        'neighborhood' => "VillaGorgona",
        'commission_percent' => 90,
        'user_id' => rand(1, 40),
    ];

});

