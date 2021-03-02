<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\User;
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

$factory->define(User::class, function (Faker $faker) {

    $rand_role_id = rand(3, 36);
    $rand_location_id = rand(1, 4);

    $nick = null;
    if($rand_role_id == 14){
        $nick = $faker->firstName.$faker->lastName;
        $rand_location_id = rand(2, 4);
    }

    return [
        'first_name' => $faker->firstName,
        'middle_name' => $faker->optional(0.3, "")->lastName,
        'last_name' => $faker->lastName,
        'nick' => $nick,
        'second_last_name' => $faker->lastName,
        'setting_role_id' => $rand_role_id,
        'setting_location_id' => $rand_location_id,
        'department_id' => 1,
        'city_id' => 1,
        'document_id' => 1,
        'birth_date' => $faker->dateTimeBetween($startDate = '-40 years', $endDate = '2002-07-15'),
        'blood_type_id' => 1,
        'address' => $faker->address,
        'email' => $faker->unique()->safeEmail,
        'email_verified_at' => now(),
        'password' => bcrypt('secret'), // password
        'remember_token' => Str::random(10),
        'theme' => 'c-app c-dark-theme',
        'current_salary' => rand(877000, 2500000),
        'document_number' => rand(1120405301, 1520405301),
        'mobile_number' => rand(1120405301, 1620405301),
        'contract_date' => $faker->dateTimeBetween($startDate = '-10 months', $endDate = 'now'),
        'admission_date' => $faker->dateTimeBetween($startDate = '-6 years', $endDate = 'now'),
        'created_at' => now(),
        'updated_at' => now(),
        'avatar' => $faker->numberBetween($min = 1, $max = 8).".".'jpg',
        'bank_account_document_id' => 1,
    ];

});
