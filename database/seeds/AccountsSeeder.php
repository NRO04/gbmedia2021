<?php

use App\Models\Satellite\SatelliteAccount;
use App\Models\Settings\SettingPage;
use App\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Faker\Generator as Faker;

class AccountsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(Faker $faker)
    {
        $models = User::where('setting_role_id', 14)->get();
        $pages = SettingPage::select('id as page_id', 'name as page_name')->take(6)->get();
        $now = Carbon::now()->format('Y-m-d H:i:s');
        $week_start = Carbon::now()->startOfWeek(Carbon::SUNDAY)->toDateString();
        $week_end = Carbon::now()->endOfWeek(Carbon::SATURDAY)->toDateString();

        foreach ($models as $model)
        {
            foreach ($pages as $page)
            {
                $accounts = [
                    [
                        'owner_id' => rand(1, 40),
                        'page_id' => $page->page_id,
                        'status_id' => 2,
                        'nick' => $model->nick,
                        'original_nick' => $model->nick,
                        'first_name' => $model->first_name,
                        'second_name' => $model->last_name,
                        'last_name' => $model->last_name,
                        'second_last_name' => $model->last_name,
                        'birth_date' => $faker->dateTimeBetween($startDate = '-40 years', $endDate = '2002-07-15'),
                        'access' => $model->email,
                        'password' => rand(1120405301, 1620405301),
                        'live_id' => $faker->optional(0.3, "")->safeEmail.", ".$faker->optional(0.3, "")->safeEmail,
                        'from_gb' => 1,
                        'user_id' => $model->id,
                        'email_sent' => 0,
                        'modified_by' => 3
                    ]
                ];

                DB::table('satellite_accounts')->insert($accounts);
            }
        }


       /* $accounts = [
            [
                'owner_id' => rand(1, 40),
                'page_id' => 2,
                'status_id' => 2,
                'nick' => "AlyssonCarter",
                'original_nick' => $model->nick,
                'first_name' => $model->first_name,
                'second_name' => $model->last_name,
                'last_name' => $model->last_name,
                'second_last_name' => $model->last_name,
                'birth_date' => $faker->dateTimeBetween($startDate = '-40 years', $endDate = '2002-07-15'),
                'access' => "LFMM1@YOPMAIL.COM",
                'password' => "Molina89",
                'live_id' => $faker->optional(0.3, "")->safeEmail.", ".$faker->optional(0.3, "")->safeEmail,
                'from_gb' => 1,
                'user_id' => 600,
                'email_sent' => 0,
                'modified_by' => 3
            ]
        ];
        DB::table('satellite_accounts')->insert($accounts);*/


        $modelos = User::where('setting_role_id', '=', 14)->take(15)->get();
        foreach ($modelos as $modelo)
        {
            $account_id = SatelliteAccount::where('user_id', '=', $modelo->id)->first();
            foreach ($pages as $page)
            {
                $accounts_statistics = [
                    [
                        'satellite_account_id' => $account_id->id,
                        'user_id' => $modelo->id,
                        'setting_page_id' => $page->page_id,
                        'setting_location_id' => $modelo->setting_location_id,
                        'value' => $faker->randomFloat($nbMaxDecimals = 2, $min = 1, $max = 50),
                        'range' => $week_start." / ".$week_end,
                        'date' => $now,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]
                ];

                DB::table('statistics')->insert($accounts_statistics);
            }
        }
    }
}
