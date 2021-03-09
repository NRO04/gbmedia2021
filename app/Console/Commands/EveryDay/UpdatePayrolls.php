<?php

namespace App\Console\Commands\EveryDay;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Payrolls\Payroll;
use App\Models\Payrolls\PayrollMovement;

class UpdatePayrolls extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:payroll';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update payroll';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {


        try {

            DB::beginTransaction();

            $this->info('Starting Cron');
            $now = Carbon::now();
            $year = intval($now->format('Y'));
            $month = intval($now->format('m'));
            $day = intval($now->format('d'));


            $activeUsers = DB::table('users')->where('status', '=', 1)->where('id', '!=', '3')->get();

            $array_users = [];

            foreach ($activeUsers as $user) {

                array_push($array_users, ["user_id" => $user->id]);

                //query for whether there is data on payrolls table
                $payroll = Payroll::where('month', '=', $month)
                    ->where('user_id', '=', $user->id)
                    ->where('year', '=', $year)
                    ->first();

                if ($payroll != null) {

                    $this->info('Existe pago en el mes actual');

                    //if data exist it will update the existing record
                    $payroll->salary2 = $user->current_salary;
                    //validating if current date is within first 15 days of the month
                    if ($day <= 15) {
                        $payroll->salary1 = $user->current_salary;
                    }
                    $payroll->save();
                } else {
                    //if it does not exist it will create a new one

                    $this->info('Creating Payroll for' . $user->id);

                    $payroll = new Payroll;
                    $payroll->user_id = $user->id;
                    $payroll->salary2 = $user->current_salary;
                    $payroll->month = $month;
                    $payroll->year = $year;
                    $payroll->salary1 = 0;
                    $payroll->worked_days1 = 0;
                    $payroll->worked_days2 = 15;
                    $payroll->created_at = $now;
                    $payroll->updated_at = $now;

                    if ($day <= 15) {

                        $this->info('Payroll quincenal');

                        $payroll->salary1 = $user->current_salary;
                        $payroll->worked_days1 = 15;
                    }
                    $payroll->save();
                }

                if ($day <= 15) {
                    $forDate = $year . '-' . $month . '-07 00:00:00';
                } else {
                    $forDate = $year . '-' . $month . '-27 00:00:00';
                }

                $payrollMovements = PayrollMovement::where('user_id', $user->id)
                    ->where('for_date', $forDate)->get();

                if ($user->contract_id == 2) {

                    $seguridadSocialSuma = ($user->current_salary) / 2;
                    $this->info('Calculating Social Security');

                    if ($payrollMovements->count()) {
                        foreach ($payrollMovements as $payrollMovement) {

                            if ($payrollMovement->payroll_type_id == 1) {

                                $seguridadSocialSuma = $seguridadSocialSuma + $payrollMovement->amount;
                            }

                            if ($payrollMovement->payroll_type_id == 14) {

                                $seguridadSocialSuma = $seguridadSocialSuma + $payrollMovement->amount;
                            }
                        }
                    }

                    $seguridadSocial = $seguridadSocialSuma * 0.08;

                    $payrollMovements = PayrollMovement::where('user_id', $user->id)
                        ->where('for_date', $forDate)->where('payroll_type_id', 12)->get();

                    $bonificacion = $user->bonus_amount;

                    if (!$payrollMovements->count()) {
                        $payrollMovement = new PayrollMovement;
                    }

                    $payrollMovement->user_id = $user->id;
                    $payrollMovement->payroll_type_id = 12;
                    $payrollMovement->amount = $seguridadSocial;
                    $payrollMovement->created_by = 3;
                    $payrollMovement->comment = "automatic";
                    $payrollMovement->for_date = $forDate;
                    $payrollMovement->automatic = 1;
                    $payrollMovement->save();

                    if ($user->has_bonus == 1) {

                        $payrollMovements = PayrollMovement::where('user_id', $user->id)
                            ->where('for_date', $forDate)->where('payroll_type_id', 13)->get();

                        $bonificacion = $user->bonus_amount;

                        if (!$payrollMovements->count()) {
                            $payrollMovement = new PayrollMovement;
                        }

                        $payrollMovement->user_id = $user->id;
                        $payrollMovement->payroll_type_id = 13;
                        $payrollMovement->amount = $bonificacion;
                        $payrollMovement->created_by = 3;
                        $payrollMovement->comment = "automatic";
                        $payrollMovement->for_date = $forDate;
                        $payrollMovement->automatic = 1;
                        $payrollMovement->save();
                    }


                    if ($user->has_transportation_aid == 1) {

                        $transportation_aid = $user->transportation_aid_amount;

                        $payrollMovements = PayrollMovement::where('user_id', $user->id)
                            ->where('for_date', $forDate)->where('payroll_type_id', 6)->get();

                        if (!$payrollMovements->count()) {
                            $payrollMovement = new PayrollMovement;
                        }

                        $payrollMovement->user_id = $user->id;
                        $payrollMovement->payroll_type_id = 6;
                        $payrollMovement->amount = $transportation_aid;
                        $payrollMovement->created_by = 3;
                        $payrollMovement->comment = "automatic";
                        $payrollMovement->for_date = $forDate;
                        $payrollMovement->automatic = 1;
                        $payrollMovement->save();
                    }
                } else {

                    if ($user->has_social_security == 1) {

                        $socialSecurity = $user->social_security_amount;

                        $payrollMovements = PayrollMovement::where('user_id', $user->id)
                            ->where('for_date', $forDate)->where('payroll_type_id', 12)->get();

                        $this->info('PayrollMovement :' . $payrollMovements->count());
                        $this->info('User_id :' . $user->id);

                        $payrollMovements = PayrollMovement::updateOrCreate([

                            'user_id' => $user->id,
                            'payroll_type_id' => 12,
                            'amount' => $socialSecurity,
                            'created_by' => 3,
                            'comment' => "automatic",
                            'automatic' => 1
                        ]);



                        // if (!$payrollMovements->count()) {
                        //     $payrollMovement = new PayrollMovement;
                        // }

                        // $payrollMovement->user_id = $user->id;
                        // $payrollMovement->payroll_type_id = 12;
                        // $payrollMovement->amount = $socialSecurity;
                        // $payrollMovement->created_by = 3;
                        // $payrollMovement->comment = "automatic";
                        // $payrollMovement->for_date = $forDate;
                        // $payrollMovement->automatic = 1;
                        // $payrollMovement->save();
                    }

                    if ($user->has_bonus == 1) {

                        $bonificacion = $user->bonus_amount;

                        $payrollMovements = PayrollMovement::where('user_id', $user->id)
                            ->where('for_date', $forDate)->where('payroll_type_id', 13)->get();

                        if (!$payrollMovements->count()) {
                            $payrollMovement = new PayrollMovement;
                        }

                        $payrollMovement->user_id = $user->id;
                        $payrollMovement->payroll_type_id = 13;
                        $payrollMovement->amount = $bonificacion;
                        $payrollMovement->created_by = 3;
                        $payrollMovement->comment = "automatic";
                        $payrollMovement->for_date = $forDate;
                        $payrollMovement->automatic = 1;
                        $payrollMovement->save();
                    }

                    if ($user->has_transportation_aid == 1) {

                        $transportation_aid = $user->transportation_aid_amount;

                        $payrollMovements = PayrollMovement::where('user_id', $user->id)
                            ->where('for_date', $forDate)->where('payroll_type_id', 6)->get();

                        if (!$payrollMovements->count()) {
                            $this->info('Creating payrolltype = 6 for', $user->id);

                            $payrollMovement = new PayrollMovement;
                        }

                        $payrollMovement->user_id = $user->id;
                        $payrollMovement->payroll_type_id = 6;
                        $payrollMovement->amount = $transportation_aid;
                        $payrollMovement->created_by = 3;
                        $payrollMovement->comment = "automatic";
                        $payrollMovement->for_date = $forDate;
                        $payrollMovement->automatic = 1;
                        $payrollMovement->save();
                    }
                }
            }


            DB::commit();
        } catch (\Throwable $th) {

            $this->info('Error Status Code:' . $th);

            DB::rollback();
        }
        $this->info('Finished Cron');
    }
}
