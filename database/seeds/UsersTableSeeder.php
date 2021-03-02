<?php

use App\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Faker\Generator as Faker;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(Faker $faker)
    {
        $user = User::create([
            'setting_role_id' => 11,
            'setting_location_id' => 1,
            'contract_id' => 2,
            'current_salary' => 900000,
            'blood_type_id' => 1,
            'first_name' => 'José',
            'middle_name' => '',
            'last_name' => 'Meza',
            'second_last_name' => '',
            'birth_date' => '1994-11-25',
            'address' => 'El coño de la madre',
            'email' => 'jmeza@admin.com',
            'email_verified_at' => now(),
            'password' => bcrypt('secret'), // password
            'remember_token' => Str::random(10),
            'theme' => 'c-app c-dark-theme',
            'created_at' => $faker->dateTimeBetween($startDate = '-18 months', $endDate = 'now'),
            'updated_at' => now(),
            'avatar' => 'jmeza.png',
            'contract_date' => $faker->dateTimeBetween($startDate = '-10 months', $endDate = 'now'),
            'department_id' => 24,
            'city_id' => 150,
            'document_id' => 4,
            'document_number' => '22940856',
            'expiration_date' => '2022-09-16',
            'personal_email' => 'jmeza.dev@gmail.com',
            'mobile_number' => '3145822702',
            'hangouts_password' => 'meza1994',
            'nationality' => 241,
            'neighborhood' => 'Santa Fé',
            'emergency_contact' => 'Mamá',
            'emergency_phone' => '123456789',
            'has_bank_account' => 0,
            'bank_account_document_id' => 4,
            'admission_date' => $faker->dateTimeBetween($startDate = '-24 months', $endDate = 'now'),
        ]);

        $user->assignRole('Programador/a');

        $user = User::create([
            'setting_role_id' => 11,
            'setting_location_id' => 1,
            'contract_id' => 2,
            'document_id' => rand(1,3),
            'current_salary' => 900000,
            'blood_type_id' => 1,
            'first_name' => 'Roman',
            'middle_name' => '',
            'last_name' => 'Rivas',
            'second_last_name' => 'Pelaez',
            'birth_date' => '2020-11-25',
            'address' => 'El papi GB',
            'email' => 'roman@admin.com',
            'email_verified_at' => now(),
            'password' => bcrypt('secret'), // password
            'remember_token' => Str::random(10),
            'theme' => 'c-app c-dark-theme',
            'created_at' => $faker->dateTimeBetween($startDate = '-18 months', $endDate = 'now'),
            'updated_at' => now(),
            'avatar' => '8.jpg',
            'contract_date' => $faker->dateTimeBetween($startDate = '-10 months', $endDate = 'now'),
            'department_id' => 1,
            'city_id' => 1,
            'document_id' => 1,
            'bank_account_document_id' => 1,
        ]);

        $user->assignRole('Programador/a');


        $user = User::create([
            'setting_role_id' => 11,
            'setting_location_id' => 2,
            'contract_id' => 2,
            'document_id' => rand(1,3),
            'current_salary' => 900000,
            'blood_type_id' => 1,
            'first_name' => 'Roman',
            'middle_name' => '',
            'last_name' => 'Monitora',
            'second_last_name' => 'Pelaez',
            'birth_date' => '2020-11-25',
            'address' => 'El papi GB',
            'email' => 'roman@monitora.com',
            'email_verified_at' => now(),
            'password' => bcrypt('secret'), // password
            'remember_token' => Str::random(10),
            'theme' => 'c-app c-dark-theme',
            'created_at' => $faker->dateTimeBetween($startDate = '-18 months', $endDate = 'now'),
            'updated_at' => now(),
            'avatar' => '8.jpg',
            'contract_date' => $faker->dateTimeBetween($startDate = '-10 months', $endDate = 'now'),
            'department_id' => 1,
            'city_id' => 1,
            'document_id' => 1,
            'bank_account_document_id' => 1,
        ]);

        $user->assignRole('Monitor/a');

        $user = User::create([
            'setting_role_id' => 2,
            'setting_location_id' => 1,
            'contract_id' => 2,
            'document_id' => rand(1,3),
            'current_salary' => 900000,
            'blood_type_id' => 1,
            'first_name' => 'Admin',
            'middle_name' => '',
            'last_name' => 'Perez',
            'second_last_name' => 'Ramirez',
            'birth_date' => $faker->dateTimeBetween($startDate = '-40 years', $endDate = '2002-07-15'),
            'address' => 'address',
            'email' => 'admin@admin.com',
            'email_verified_at' => now(),
            'password' => bcrypt('secret'), // password
            'remember_token' => Str::random(10),
            'theme' => 'c-app c-dark-theme',
            'created_at' => $faker->dateTimeBetween($startDate = '-18 months', $endDate = 'now'),
            'updated_at' => now(),
            'avatar' => '8.jpg',
            'contract_date' => $faker->dateTimeBetween($startDate = '-10 months', $endDate = 'now'),
            'department_id' => 1,
            'city_id' => 1,
            'document_id' => 1,
            'bank_account_document_id' => 1,
        ]);

        $user->assignRole('Gerente');

        $user = User::create([
            'setting_role_id' => 1,
            'setting_location_id' => 1,
            'contract_id' => rand(1,2),
            'document_id' => rand(1,3),
            'current_salary' => rand(877803,2000000),
            'blood_type_id' => 1,
            'first_name' => 'Asistente',
            'middle_name' => '',
            'last_name' => 'Perez',
            'second_last_name' => 'Ramirez',
            'birth_date' => $faker->dateTimeBetween($startDate = '-40 years', $endDate = '2002-07-15'),
            'address' => 'address',
            'email' => 'asistente@admin.com',
            'email_verified_at' => now(),
            'password' => bcrypt('secret'), // password
            'remember_token' => Str::random(10),
            'theme' => 'c-app c-dark-theme',
            'created_at' => $faker->dateTimeBetween($startDate = '-18 months', $endDate = 'now'),
            'updated_at' => now(),
            'avatar' => '7.jpg',
            'contract_date' => $faker->dateTimeBetween($startDate = '-10 months', $endDate = 'now'),
            'department_id' => 1,
            'city_id' => 1,
            'document_id' => 1,
            'bank_account_document_id' => 1,
        ]);

        $user->assignRole('Asistente Administrativo');

        for ($i=0; $i < 50 ; $i++)
        {
            $rand_location_id = rand(2, 4);
            $nick = $faker->firstName.$faker->lastName;

            User::create([
                'first_name' => $faker->firstName,
                'middle_name' => $faker->optional(0.3, "")->lastName,
                'last_name' => $faker->lastName,
                'nick' => $nick,
                'second_last_name' => $faker->lastName,
                'setting_role_id' => 14,
                'current_salary' => rand(877803,2000000),
                'setting_location_id' => $rand_location_id,
                'document_id' => rand(1,3),
                'contract_id' => rand(1,2),
                'birth_date' => $faker->dateTimeBetween($startDate = '-40 years', $endDate = '2002-07-15'),
                'blood_type_id' => 1,
                'address' => $faker->address,
                'email' => $faker->unique()->safeEmail,
                'email_verified_at' => now(),
                'password' => bcrypt('secret'), // password
                'remember_token' => Str::random(10),
                'theme' => 'c-app c-dark-theme',
                'contract_date' => $faker->dateTimeBetween($startDate = '-10 months', $endDate = 'now'),
                'created_at' => $faker->dateTimeBetween($startDate = '-18 months', $endDate = 'now'),
                'updated_at' => now(),
                'avatar' => $faker->numberBetween($min = 1, $max = 8).".".'jpg',
                'department_id' => 1,
                'city_id' => 1,
                'document_id' => 1,
                'bank_account_document_id' => 1,
            ]);

            $user->assignRole('Modelo');
        }

        for ($i=0; $i < 10 ; $i++)
        {
            $rand_location_id = rand(2, 4);
            $nick = "NULL";

            User::create([
                'first_name' => $faker->firstName,
                'middle_name' => $faker->optional(0.3, "")->lastName,
                'last_name' => $faker->lastName,
                'nick' => $nick,
                'second_last_name' => $faker->lastName,
                'setting_role_id' => 6,
                'current_salary' => rand(877803,2000000),
                'setting_location_id' => $rand_location_id,
                'document_id' => rand(1,3),
                'contract_id' => rand(1,2),
                'birth_date' => $faker->dateTimeBetween($startDate = '-40 years', $endDate = '2002-07-15'),
                'blood_type_id' => 1,
                'address' => $faker->address,
                'email' => $faker->unique()->safeEmail,
                'email_verified_at' => now(),
                'password' => bcrypt('secret'), // password
                'remember_token' => Str::random(10),
                'theme' => 'c-app c-dark-theme',
                'contract_date' => $faker->dateTimeBetween($startDate = '-10 months', $endDate = 'now'),
                'created_at' => $faker->dateTimeBetween($startDate = '-18 months', $endDate = 'now'),
                'updated_at' => now(),
                'avatar' => $faker->numberBetween($min = 1, $max = 8).".".'jpg',
                'department_id' => 1,
                'city_id' => 1,
                'document_id' => 1,
                'bank_account_document_id' => 1,
            ]);

            $user->assignRole('Monitor/a');
        }

        $user = User::create([
            'setting_role_id' => 11,
            'setting_location_id' => 1,
            'contract_id' => 2,
            'document_id' => rand(1,3),
            'current_salary' => 1000,
            'blood_type_id' => 1,
            'first_name' => 'Ludwig',
            'middle_name' => '',
            'last_name' => 'Romana',
            'second_last_name' => 'Romana',
            'birth_date' => '2020-11-25',
            'address' => 'Nowhere Bitch',
            'email' => 'ludwig@programador.com',
            'email_verified_at' => now(),
            'password' => bcrypt('secret'), // password
            'remember_token' => Str::random(10),
            'theme' => 'c-app c-dark-theme',
            'created_at' => $faker->dateTimeBetween($startDate = '-18 months', $endDate = 'now'),
            'updated_at' => now(),
            'avatar' => 'ludwig.png',
            'contract_date' => $faker->dateTimeBetween($startDate = '-10 months', $endDate = 'now'),
            'department_id' => 1,
            'city_id' => 1,
            'document_id' => 1,
            'bank_account_document_id' => 1,
        ]);

        $user->assignRole('Programador/a');
    }

}
