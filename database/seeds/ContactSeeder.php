<?php

use Illuminate\Database\Seeder;
use Faker\Generator as Faker;

class ContactSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(Faker $faker)
    {
        for ($i=0; $i < 200 ; $i++)
        {
            $rand_user_id = rand(2, 10);
            $rand_owner_id = rand(10, 20);
            $rand_from = rand(2, 4);
            $email = $faker->unique()->safeEmail;

            \App\Models\Contacts\Contact::create([
                'contact_email' => $email,
                'contact_from' => $rand_from,
                'modified_by' => $rand_user_id,
                'owner_id' => $rand_owner_id,
                'created_at' => \Carbon\Carbon::now(),
                'updated_at' => \Carbon\Carbon::now(),
            ]);
        }
    }
}
