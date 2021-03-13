<?php

namespace App\Http\Controllers\Payrolls;

use App\Exports\Payroll\PABFormat;
use App\Exports\Payroll\PABFormatSheet;
use App\Http\Controllers\Controller;
use App\Models\HumanResources\RHExtraHours;
use App\Models\HumanResources\RHExtraValue;
use App\Models\Payrolls\PayrollBoutique;
use App\Models\Payrolls\PayrollBoutiqueInstallment;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use App\User;
use App\Models\Payrolls\Payroll;
use App\Models\Payrolls\PayrollMovement;
use App\Models\Payrolls\PayrollIncrease;
use DB;
use Auth;
use DataTables;
use Carbon\Carbon;
use App\Traits\TraitGlobal;
use App\Exports\Payroll\SalaryIncrease;
use Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Facades\Excel;
use TCPDF;

class PayrollController extends Controller
{
    public function payrollMovements($user_id, $date_from, $date_to, $type)
    {
        $result = PayrollMovement::where('user_id', $user_id)->where('payroll_type_id', $type)->whereBetween('for_date', [$date_from, $date_to])->sum('amount');
        return $result;
    }

    public function caculatedSocialSecurity($user_id, $extras, $night_surcharge, $salary_half)
    {

        $userInfo = User::find($user_id);

        $hasSocial_security = ($userInfo->has_social_security == 1) ? 1 : 0; // Verficia si el usuario tiene seguridad social.
        $contractType = $userInfo->contract_id; // Tipo de contrato del usuario.


        $social_security_amount = (empty($userInfo->social_security_amount)) ? 0 : $userInfo->social_security_amount; //Valor de seguridad social desde bd.



        switch ($contractType) {
            case 1:

                return
                    $this->provisionServices($hasSocial_security, $social_security_amount);
                break;
            case 2:

                return $this->indefiniteTerm($extras, $night_surcharge, $salary_half);
                break;

            default:
        }
    }

    /**
     *  Funcion que calcula seguridad social en contratos de termnos Indefinido.
     */
    public function indefiniteTerm($extras, $night_surcha, $salary_hal)
    {
        return ($salary_hal + $extras + $night_surcha) * 0.08;
    }

    /**
     *  Funcion que calcula seguridad social en contratos de Prestacion servicios.
     */
    public function provisionServices($hasSocialSecurity, $social_security_amount)
    {

        switch ($hasSocialSecurity) {
            case 1:

                return $social_security_amount / 2;
                break;
            case 0:

                return 0;
                break;

            default:
        }
    }

    /**
     * LLamar a esta función y pasarle el user_id para recalcular la nómina de la quincena actual (Salario, seguridad social, etc)
     */
    public function calculateUserPayroll($user_id)
    {
        $now = Carbon::now()->day;
        $last_day_of_month = Carbon::now()->endOfMonth()->day;

        $first_for_date = Carbon::now()->year . "-" . Carbon::now()->month . "-07";
        $second_for_date = Carbon::now()->year . "-" . Carbon::now()->month . "-27";

        if (($now >= 1 && $now <= 14) || ($now == $last_day_of_month)) {
            $quarter = 1;
            $for_date = Carbon::now()->year . "-" . Carbon::now()->month . "-07";

            if ($now == $last_day_of_month) {
                $date = Carbon::now()->addDay();
                $for_date = $date->year . "-" . $date->month . "-07";
            }
        } else {
            $for_date = Carbon::now()->year . "-" . Carbon::now()->month . "-27";
            $quarter = 2;
        }

        $payroll_controller = new PayrollController();

        // Get or create user payroll
        $user = User::find($user_id);

        $payroll = Payroll::firstOrCreate(
            [
                'user_id' => $user_id,
                'month' => Carbon::now()->month,
                'year' => Carbon::now()->year,
            ],
            [
                'user_id' => $user_id,
                'month' => Carbon::now()->month,
                'year' => Carbon::now()->year,
                'salary1' => $user->current_salary,
                'worked_days1' => 15,
                'salary2' => $user->current_salary,
                'worked_days2' => 15,
            ]
        );

        if ($quarter == 1) {
            // First quarter
            $date_from = Carbon::now()->year . "-" . Carbon::now()->month . "-01";
            $date_to = Carbon::now()->year . "-" . Carbon::now()->month . "-15";

            $extra_hours = $payroll_controller->payrollMovements($user_id, $date_from, $date_to, 14);
            $night_surcharge = $payroll_controller->payrollMovements($user_id, $date_from, $date_to, 1);
            $current_salary = $payroll->salary1 / 2;

            $quarter_salary = round(($current_salary / 15) * $payroll->worked_days1);
            $social_security_amount = round(($extra_hours + $night_surcharge + $quarter_salary) * 0.08);

            $payroll_movement = PayrollMovement::firstOrCreate(
                [
                    'user_id' => $user_id,
                    'payroll_type_id' => 12,
                    'for_date' => $first_for_date
                ],
                [
                    'user_id' => $user_id,
                    'payroll_type_id' => 12,
                    'for_date' => $first_for_date,
                    'amount' => $social_security_amount,
                    'created_by' => Auth::user()->id,
                    'comment' => 'Modificado valor Seguridad Social',
                    'automatic' => 1,
                ]
            );

            $payroll_movement->amount = $social_security_amount;
            $payroll_movement->created_by = Auth::user()->id;
            $payroll_movement->comment = "Modificado valor Seguridad Social";
            $payroll_movement->automatic = 1;
            $payroll_movement->save();

            // Second quarter
            $last_day_of_month = Carbon::now()->endOfMonth()->day;

            $date_from = Carbon::now()->year . "-" . Carbon::now()->month . "-16";
            $date_to = Carbon::now()->year . "-" . Carbon::now()->month . "-" . $last_day_of_month;

            $extra_hours = $payroll_controller->payrollMovements($user_id, $date_from, $date_to, 14);
            $night_surcharge = $payroll_controller->payrollMovements($user_id, $date_from, $date_to, 1);
            $current_salary = $payroll->salary2 / 2;

            $quarter_salary = round(($current_salary / 15) * $payroll->worked_days2);
            $social_security_amount = round(($extra_hours + $night_surcharge + $quarter_salary) * 0.08);

            $payroll_movement = PayrollMovement::firstOrCreate(
                [
                    'user_id' => $user_id,
                    'payroll_type_id' => 12,
                    'for_date' => $first_for_date
                ],
                [
                    'user_id' => $user_id,
                    'payroll_type_id' => 12,
                    'for_date' => $first_for_date,
                    'amount' => $social_security_amount,
                    'created_by' => Auth::user()->id,
                    'comment' => 'Modificado valor Seguridad Social',
                    'automatic' => 1,
                ]
            );

            $payroll_movement->amount = $social_security_amount;
            $payroll_movement->created_by = Auth::user()->id;
            $payroll_movement->comment = "Modificado valor Seguridad Social";
            $payroll_movement->automatic = 1;
            $payroll_movement->save();
        }

        if ($quarter == 2) {
            // Only update the second quarter of month
            $last_day_of_month = Carbon::now()->endOfMonth()->day;

            $date_from = Carbon::now()->year . "-" . Carbon::now()->month . "-16";
            $date_to = Carbon::now()->year . "-" . Carbon::now()->month . "-" . $last_day_of_month;

            $extra_hours = $payroll_controller->payrollMovements($user_id, $date_from, $date_to, 14);
            $night_surcharge = $payroll_controller->payrollMovements($user_id, $date_from, $date_to, 1);
            $current_salary = $payroll->salary2 / 2;

            $quarter_salary = round(($current_salary / 15) * $payroll->worked_days2);
            $social_security_amount = round(($extra_hours + $night_surcharge + $quarter_salary) * 0.08);

            $payroll_movement = PayrollMovement::firstOrCreate(
                [
                    'user_id' => $user_id,
                    'payroll_type_id' => 12,
                    'for_date' => $second_for_date
                ],
                [
                    'user_id' => $user_id,
                    'payroll_type_id' => 12,
                    'for_date' => $second_for_date,
                    'amount' => $social_security_amount,
                    'created_by' => Auth::user()->id,
                    'comment' => 'Modificado valor Seguridad Social',
                    'automatic' => 1,
                ]
            );

            $payroll_movement->amount = $social_security_amount;
            $payroll_movement->created_by = Auth::user()->id;
            $payroll_movement->comment = "Modificado valor Seguridad Social";
            $payroll_movement->automatic = 1;
            $payroll_movement->save();
        }
    }

    public function saveSocialSecurity(Request $request)
    {
        $this->validate(
            $request,
            [
                'social_security' => 'required|numeric',
            ],
            [
                'social_security.required' => 'Este campo es obligatorio',
                'social_security.numeric' => 'Este campo debe ser numérico',
            ]
        );

        try {
            DB::beginTransaction();

            $for_date = ($request->quarter == 1) ? "07 00:00:00" : "27 00:00:00";
            $for_date = $request->year . "-" . $request->month . "-" . $for_date;

            $movements = PayrollMovement::updateOrInsert([
                'user_id' => $request->user_id,
                'payroll_type_id' => 12,
                'for_date' => $for_date
            ], [
                'user_id' => $request->user_id,
                'payroll_type_id' => 12,
                'amount' => $request->social_security,
                'created_by' => Auth::user()->id,
                'comment' => "Social Security",
                'for_date' => $for_date,
                'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
            ]);

            DB::commit();
            return response()->json(['success' => true]);
        } catch (Exception $e) {
            DB::rollback();

            return response()->json(['success' => false]);
        }
    }

    //nuevas

    public function getRanges()
    {
        $ranges = Payroll::select('month', 'year')->groupBy('month', 'year')->orderBy('year', 'DESC')->orderBy('month', 'DESC')->get();
        $result = [];
        $cont = 0;
        foreach ($ranges as $range) {
            $month = Carbon::parse('01-' . $range->month . '-' . $range->year)->format('M');

            $result[$cont]['month'] = $range->month;
            $result[$cont]['year'] = $range->year;
            $result[$cont]['quarter'] = 2;
            $result[$cont]['range'] = "16-" . cal_days_in_month(CAL_GREGORIAN, $range->month, $range->year) . " " . $month . " " . $range->year;
            $cont++;

            $result[$cont]['month'] = $range->month;
            $result[$cont]['year'] = $range->year;
            $result[$cont]['quarter'] = 1;
            $result[$cont]['range'] = "01-15 " . $month . " " . $range->year;
            $cont++;
        }
        return response()->json($result);
    }

    public function getPayroll(Request $request)
    {
        $exists = Payroll::where('user_id', $request->user_id)->where('month', $request->month)->where('year', $request->year)->exists();
        $user = User::where('id', $request->user_id)->first();

        if ($exists) {
            $payroll = Payroll::where('user_id', $request->user_id)->where('month', $request->month)->where('year', $request->year)->get(); //Nomina
            $salary = ($request->quarter == 1) ? $payroll[0]->salary1 :  $payroll[0]->salary2; // Salario
            $worked_days = ($request->quarter == 1) ? $payroll[0]->worked_days1 :  $payroll[0]->worked_days2; //Dias trabajados
            $salary_half = $salary / 2; // Salario quincenal de la nomina.
            $amount = $salary_half / 15; //Salario quincenal / 15 Esto calcula el valor diario que se gana el usuario.
            $amount = $amount * $worked_days; //

            $total_salary_half =  $amount; // Salario Dias trabajados.
            $basic_quarter = $amount;

            $date_from = ($request->quarter == 1) ? $request->year . "-" . $request->month . "-01 00:00:00" : $request->year . "-" . $request->month . "-16 00:00:00";
            $date_to = ($request->quarter == 1) ? $request->year . "-" . $request->month . "-15 23:59:59" : $request->year . "-" . $request->month . "-" . cal_days_in_month(CAL_GREGORIAN, $request->month, $request->year) . " 23:59:59";

            $night_surcharge = $this->payrollMovements($request->user_id, $date_from, $date_to, 1);
            $commissions = $this->payrollMovements($request->user_id, $date_from, $date_to, 2);
            //$movilization_help = $this->payrollMovements($request->user_id, $date_from, $date_to, 3);
            $movilization_help = $user->mobilization_amount / 2;
            $record = $this->payrollMovements($request->user_id, $date_from, $date_to, 4);
            $bonus = $this->payrollMovements($request->user_id, $date_from, $date_to, 5);
            //$bonus_extra = $this->payrollMovements($request->user_id, $date_from, $date_to, 13);
            $bonus_extra = $user->bonus_amount / 2;
            //$transportation_help = $this->payrollMovements($request->user_id, $date_from, $date_to, 6);
            $transportation_help = $user->transportation_aid_amount;
            $extra_hours = $this->payrollMovements($request->user_id, $date_from, $date_to, 14);

            $sums_amount = $night_surcharge + $commissions + $movilization_help + $record + $bonus + $transportation_help + $extra_hours; //Total Devengado



            $social_security =  $this->caculatedSocialSecurity($request->user_id, $extra_hours, $night_surcharge, $salary_half); //Total Seguridad Social.


            $loan = $this->payrollMovements($request->user_id, $date_from, $date_to, 15);
            $food = $this->payrollMovements($request->user_id, $date_from, $date_to, 7) + 0;
            $fridge = $this->payrollMovements($request->user_id, $date_from, $date_to, 8);
            $boutique = $this->payrollMovements($request->user_id, $date_from, $date_to, 11);
            $others = $this->payrollMovements($request->user_id, $date_from, $date_to, 9);
            $late_arrival = $this->payrollMovements($request->user_id, $date_from, $date_to, 10);

            $deductions_amount = $social_security + $loan + $food + $fridge + $boutique + $others + $late_arrival; //Total deducciones

            $amount = $amount + ($sums_amount - $deductions_amount); //Valor neto 

            $total_accrued = $basic_quarter + $sums_amount; //Formula Funcional


            $result = [
                "user_id" => $request->user_id,
                "user_fullname" => $payroll[0]->user->userFullName(),
                "salary" => $salary,
                "salary_half" => $total_salary_half,
                "worked_days" => $worked_days,
                "extra_hours" => $extra_hours,
                "night_surcharge" => $night_surcharge,
                "commissions" => $commissions,
                "movilization_help" => $movilization_help,
                "record" => $record,
                "bonus" => $bonus,
                "transportation_help" => $transportation_help,
                "social_security" => $social_security,
                "loan" => $loan,
                "food" => $food,
                "fridge" => $fridge,
                "boutique" => $boutique,
                "others" => $others,
                "late_arrival" => $late_arrival,
                "sums_amount" => $sums_amount,
                "deductions_amount" => $deductions_amount,
                "bonus_extra" => $bonus_extra,
                "amount" => $amount,
                "quarter" => $request->quarter,
                "month" => $request->month,
                "year" => $request->year,
                "basic_quarter" => $basic_quarter,
                "total_accrued" => $total_accrued,
            ];
            /*if ($request->user_id == 1) {
                dd($date_from);
                dd($request->quarter);
                dd($result);
            }*/
        } else {
            $result = [
                "user_id" => $request->user_id,
                "user_fullname" => "",
                "salary" => 0,
                "salary_half" => 0,
                "worked_days" => 0,
                "extra_hours" => 0,
                "night_surcharge" => 0,
                "commissions" => 0,
                "movilization_help" => 0,
                "record" => 0,
                "bonus" => 0,
                "transportation_help" => 0,
                "social_security" => 0,
                "loan" => 0,
                "food" => 0,
                "fridge" => 0,
                "boutique" => 0,
                "others" => 0,
                "late_arrival" => 0,
                "sums_amount" => 0,
                "deductions_amount" => 0,
                "bonus_extra" => 0,
                "amount" => 0,
            ];
        }

        return $result;
    }

    public function getPayrolls(Request $request)
    {
        $quarter = ($request->quarter == 0 || ($request->quarter != 1 && $request->quarter != 2)) ? ((date("d") <= 15) ? 1 : 2)  : $request->quarter;
        $month = ($request->month != 0 && ($request->month >= 1 && $request->month <= 12)) ?  $request->month : date("m");
        $year = ($request->year != 0 && ($request->year >= 2015 && $request->year <= 2060)) ?  $request->year : date("Y");

        $filter_payroll["quarter"] = $quarter;
        $filter_payroll["month"] = $month;
        $filter_payroll["year"] = $year;

        $filter_payroll["quarter"] = $quarter;
        $filter_payroll["month"] = $month;
        $filter_payroll["year"] = $year;

        $range = $quarter . "-" . $month . "-" . $year;

        $payrolls = Payroll::where('month', $month)->where('year', $year)->get();

        $cont = 0;
        $result = [];
        foreach ($payrolls as $key => $value) {

            $requestObj = new Request(array("user_id" => $value->user_id, "quarter" => $quarter, "month" => $month, "year" => $year,));
            $payroll = $this->getPayroll($requestObj);

            $result[$cont]["id"] = $value->id;
            $result[$cont]["user_id"] = $value->user->id;
            $result[$cont]["user"] = $value->user->userFullName();
            $result[$cont]["avatar"] = is_null($value->user->avatar) ? asset("images/svg/no-photo.svg") : global_asset("../storage/app/public/" . tenant('studio_slug') . "/avatars/" . $value->user->avatar);
            $result[$cont]["role"] = $value->user->role->name;
            $result[$cont]["salary"] = $payroll['salary'];
            $result[$cont]["salary_half"] = $payroll['salary_half'];
            $result[$cont]["worked_days"] = $payroll['worked_days'];
            $result[$cont]["sums_amount"] = $payroll['sums_amount'];
            $result[$cont]["deductions_amount"] = $payroll['deductions_amount'];
            $result[$cont]["bonus_extra"] = $payroll['bonus_extra'];
            $result[$cont]["amount"] = $payroll['amount'];
            //            $result[$cont]["last_increase"] = Carbon::parse($value->created_at, 'UTC')->isoFormat('D MMM YYYY');
            $result[$cont]["last_increase"] = null;
            $result[$cont]["payroll"] = $payroll;
            $cont++;
        }

        return response()->json(["payrolls" => $result, "filter_payroll" => $filter_payroll, "range" => $range]);
    }

    public function getPayrollsBoutique(Request $request)
    {
        $payrolls = PayrollBoutique::where('status', $request->status)->get();

        $cont = 0;
        $result = [];
        foreach ($payrolls as $key => $value) {

            $result[$cont]["id"] = $value->id;
            $result[$cont]["status"] = $value->status;
            $result[$cont]["user_id"] = $value->user->id;
            $result[$cont]["user"] = $value->user->userFullName();
            $result[$cont]["avatar"] = is_null($value->user->avatar) ? asset("images/svg/no-photo.svg") : global_asset("../storage/app/public/" . tenant('studio_slug') . "/avatars/" .
                $value->user->avatar);
            $result[$cont]["amount"] = $value->amount;
            $result[$cont]["installment"] = $value->amount - PayrollBoutiqueInstallment::where('payroll_boutique_id', $value->id)->sum('installment');
            $result[$cont]["should_pay"] = $value->installment;
            $result[$cont]["comment"] = $value->comment;
            $result[$cont]["created_at"] = Carbon::parse($value->created_at, 'UTC')->isoFormat('D MMM YYYY');
            $cont++;
        }

        return response()->json(["payrolls" => $result]);
    }

    public function getBoutiqueInstallments(Request $request)
    {
        $payroll = PayrollBoutique::find($request->id);
        $installments = PayrollBoutiqueInstallment::where('payroll_boutique_id', $payroll->id)->get();

        $cont = 0;
        $result = [];
        $total = $payroll->amount;
        foreach ($installments as $key => $installment) {

            $result[$cont]["created_at"] = Carbon::parse($installment->created_at, 'UTC')->isoFormat('D MMM YYYY');
            $result[$cont]["installment"] = $installment->installment;
            $total = $total - $installment->installment;
            $result[$cont]["total"] = $total;
            $result[$cont]["created_by"] = $installment->created_by_user->userFullName();
            $cont++;
        }

        return response()->json(["installments" => $result]);
    }

    public function infoMovements(Request $request)
    {
        $for_date = ($request->quarter == 1) ? "07 00:00:00" : "27 00:00:00";
        $for_date = $request->year . "-" . $request->month . "-" . $for_date;

        $movements = PayrollMovement::where('user_id', $request->user_id)->where('payroll_type_id', $request->payroll_type_id)->where('for_date', $for_date)->get();

        $result = "<table class='table table-hover table-striped'>
                    <thead>
                        <tr>
                            <th>Fecha Creacion</th>
                            <th>Valor</th>
                            <th>Observacion</th>
                            <th>Creado Por</th>
                        </tr>
                    </thead>
                    <tbody>";

        foreach ($movements as $movement) {
            $result = $result . "<tr>";

            $date = Carbon::parse($movement->created_at, 'UTC');
            $date = $date->isoFormat('D MMM YYYY');
            $result = $result . "<td>" . $date . "</td>";
            $result = $result . "<td>$ " . $movement->amount . "</td>";
            $result = $result . "<td>" . $movement->comment . "</td>";
            $result = $result . "<td>" . $movement->created_by_user->userFullName() . "</td>";
            $result = $result . "</tr>";
        }
        $result = $result . "</tbody></table>";

        return $result;
    }

    public function increasesExport(Request $request)
    {
        $result = [];
        $quarter = ($request->quarter == 0 || ($request->quarter != 1 && $request->quarter != 2)) ? ((date("d") <= 15) ? 1 : 2)  : $request->quarter;
        $month = ($request->month != 0 && ($request->month >= 1 && $request->month <= 12)) ?  $request->month : date("m");
        $year = ($request->year != 0 && ($request->year >= 2015 && $request->year <= 2060)) ?  $request->year : date("Y");

        $date_mask = ($quarter == 1) ? $year . "-" . $month . "-07" : $year . "-" . $month . "-27";
        $range = ($quarter == 1) ? "01-15 " . Carbon::parse($date_mask, 'UTC')->isoFormat('MMMM YYYY') : "16-" . cal_days_in_month(CAL_GREGORIAN, $month, $year) . " " . Carbon::parse($date_mask, 'UTC')->isoFormat('MMMM YYYY');
        $users = User::with(['role'])->where('status', 1)->where('setting_role_id', '!=', 14)->orderBy('setting_role_id', 'ASC')->get();

        foreach ($users as $key => $user) {

            $result["increase"][$key]["full_name"] = $user->first_name . " " . $user->last_name . " " . $user->second_last_name;
            $result["increase"][$key]["role"] = $user->role->name;
            $result["increase"][$key]["admission_date"] = Carbon::parse($user->admission_date, 'UTC')->isoFormat('D MMM YYYY');
            $result["increase"][$key]["salary"] = $user->current_salary;

            $increase = PayrollIncrease::where('user_id', $user->id)->latest()->first();
            $result["increase"][$key]["increase"] = ($increase == null) ? "" : Carbon::parse($increase->created_at, 'UTC')->isoFormat('D MMM YYYY');

            $result["salary"][$key]["full_name"] = $user->first_name . " " . $user->last_name . " " . $user->second_last_name;
            $result["salary"][$key]["role"] = $user->role->name;
            $result["salary"][$key]["admission_date"] = Carbon::parse($user->admission_date, 'UTC')->isoFormat('D MMMM YYYY');
            $result["salary"][$key]["document_number"] = $user->document_number;
            $result["salary"][$key]["address"] = $user->address;
            $result["salary"][$key]["mobile_number"] = $user->mobile_number;

            $requestObj = new Request(array("user_id" => $user->id, "quarter" => $quarter, "month" => $month, "year" => $year));
            $payroll = $this->getPayroll($requestObj);
            $result["salary"][$key]["salary"] = $payroll['salary'];
            $result["salary"][$key]["worked_days"] = $payroll['worked_days'];
            $result["salary"][$key]["salary_half"] = $payroll['salary_half'];
            $result["salary"][$key]["extra_hours"] = $payroll['extra_hours'];
            $result["salary"][$key]["night_surcharge"] = $payroll['night_surcharge'];
            $result["salary"][$key]["commissions"] = $payroll['commissions'];
            $result["salary"][$key]["movilization_help"] = $payroll['movilization_help'];
            $result["salary"][$key]["record"] = $payroll['record'];
            $result["salary"][$key]["bonus"] = $payroll['bonus'];
            $result["salary"][$key]["transportation_help"] = $payroll['transportation_help'];
            $result["salary"][$key]["sums_amount"] = $payroll['sums_amount'];
            $result["salary"][$key]["social_security"] = $payroll['social_security'];
            $result["salary"][$key]["loan"] = $payroll['loan'];
            $result["salary"][$key]["food"] = $payroll['food'];
            $result["salary"][$key]["fridge"] = $payroll['fridge'];
            $result["salary"][$key]["boutique"] = $payroll['boutique'];
            $result["salary"][$key]["others"] = $payroll['others'];
            $result["salary"][$key]["late_arrival"] = $payroll['late_arrival'];
            $result["salary"][$key]["deductions_amount"] = $payroll['deductions_amount'];
            $result["salary"][$key]["amount"] = $payroll['amount'];
        }

        return Excel::download(new SalaryIncrease($result, $range), 'salario_aumento(' . $range . ').xlsx');
    }

    public function staffExport(Request $request)
    {
        $result = [];
        $quarter = ($request->quarter == 0 || ($request->quarter != 1 && $request->quarter != 2)) ? ((date("d") <= 15) ? 1 : 2)  : $request->quarter;
        $month = ($request->month != 0 && ($request->month >= 1 && $request->month <= 12)) ?  $request->month : date("m");
        $year = ($request->year != 0 && ($request->year >= 2015 && $request->year <= 2060)) ?  $request->year : date("Y");

        $date_mask = ($quarter == 1) ? $year . "-" . $month . "-07" : $year . "-" . $month . "-27";
        $range = ($quarter == 1) ? "01-15 " . Carbon::parse($date_mask, 'UTC')->isoFormat('MMMM YYYY') : "16-" . cal_days_in_month(CAL_GREGORIAN, $month, $year) . " " . Carbon::parse($date_mask, 'UTC')->isoFormat('MMMM YYYY');
        $users = User::with(['role'])->where('status', 1)->where('setting_role_id', '!=', 14)->orderBy('setting_role_id', 'ASC')->get();

        foreach ($users as $key => $user) {

            $result["increase"][$key]["full_name"] = $user->first_name . " " . $user->last_name . " " . $user->second_last_name;
            $result["increase"][$key]["role"] = $user->role->name;
            $result["increase"][$key]["admission_date"] = Carbon::parse($user->admission_date, 'UTC')->isoFormat('D MMM YYYY');
            $result["increase"][$key]["salary"] = $user->current_salary;

            $increase = PayrollIncrease::where('user_id', $user->id)->latest()->first();
            $result["increase"][$key]["increase"] = ($increase == null) ? "" : Carbon::parse($increase->created_at, 'UTC')->isoFormat('D MMM YYYY');

            $result["salary"][$key]["full_name"] = $user->first_name . " " . $user->last_name . " " . $user->second_last_name;
            $result["salary"][$key]["role"] = $user->role->name;
            $result["salary"][$key]["admission_date"] = Carbon::parse($user->admission_date, 'UTC')->isoFormat('D MMMM YYYY');
            $result["salary"][$key]["document_number"] = $user->document_number;
            $result["salary"][$key]["address"] = $user->address;
            $result["salary"][$key]["mobile_number"] = $user->mobile_number;

            $requestObj = new Request(array("user_id" => $user->id, "quarter" => $quarter, "month" => $month, "year" => $year));
            $payroll = $this->getPayroll($requestObj);
            $result["salary"][$key]["salary"] = $payroll['salary'];
            $result["salary"][$key]["worked_days"] = $payroll['worked_days'];
            $result["salary"][$key]["salary_half"] = $payroll['salary_half'];
            $result["salary"][$key]["extra_hours"] = $payroll['extra_hours'];
            $result["salary"][$key]["night_surcharge"] = $payroll['night_surcharge'];
            $result["salary"][$key]["commissions"] = $payroll['commissions'];
            $result["salary"][$key]["movilization_help"] = $payroll['movilization_help'];
            $result["salary"][$key]["record"] = $payroll['record'];
            $result["salary"][$key]["bonus"] = $payroll['bonus'];
            $result["salary"][$key]["transportation_help"] = $payroll['transportation_help'];
            $result["salary"][$key]["sums_amount"] = $payroll['sums_amount'];
            $result["salary"][$key]["social_security"] = $payroll['social_security'];
            $result["salary"][$key]["loan"] = $payroll['loan'];
            $result["salary"][$key]["food"] = $payroll['food'];
            $result["salary"][$key]["fridge"] = $payroll['fridge'];
            $result["salary"][$key]["boutique"] = $payroll['boutique'];
            $result["salary"][$key]["others"] = $payroll['others'];
            $result["salary"][$key]["late_arrival"] = $payroll['late_arrival'];
            $result["salary"][$key]["deductions_amount"] = $payroll['deductions_amount'];
            $result["salary"][$key]["amount"] = $payroll['amount'];
        }

        return Excel::download(new SalaryIncrease($result, $range), 'salario_aumento(' . $range . ').xlsx');
    }

    public function pabExport(Request $request)
    {
        $result = [];
        $quarter = ($request->quarter == 0 || ($request->quarter != 1 && $request->quarter != 2)) ? ((date("d") <= 15) ? 1 : 2)  : $request->quarter;
        $month = ($request->month != 0 && ($request->month >= 1 && $request->month <= 12)) ?  $request->month : date("m");
        $year = ($request->year != 0 && ($request->year >= 2015 && $request->year <= 2060)) ?  $request->year : date("Y");

        $date_mask = ($quarter == 1) ? $year . "-" . $month . "-07" : $year . "-" . $month . "-27";
        $range = ($quarter == 1) ? "01-15 " . Carbon::parse($date_mask, 'UTC')
            ->isoFormat('MMMM YYYY') : "16-" . cal_days_in_month(CAL_GREGORIAN, $month, $year) . " " . Carbon::parse($date_mask, 'UTC')->isoFormat('MMMM YYYY');

        //$users = User::with(['role'])->where('status', 1)->where('setting_role_id', '!=', 14)->orderBy('setting_role_id', 'ASC')->get();
        $payrolls = Payroll::where('month', $month)->where('year', $year)->get();

        $reference = "L000000";
        $count_pab = 0;
        $count_grupo_aval = 0;
        $count_no_payment_method = 0;
        $result["pab"] = [];
        $result["grupo_aval"] = [];
        $result["no_payment_method"] = [];

        foreach ($payrolls as $pay) {

            if ($pay->user->status == 0) {
                continue;
            }
            $full_name = substr(($pay->user->first_name . " " . $pay->user->last_name . " " . $pay->user->second_last_name), 0, 30);
            $full_name = str_replace("ñ", "n", $full_name);
            $bank_account_document_id = $pay->user->bank_account_document_id;
            $bank_account_document_number = $pay->user->bank_account_document_number;
            $requestObj = new Request(array(
                "user_id" => $pay->user_id, "quarter" => $quarter,
                "month" => $month, "year" => $year
            ));
            $payroll = $this->getPayroll($requestObj);

            if ($pay->user->has_bank_account == 0) {
                $result["no_payment_method"][$count_no_payment_method]["full_name"] = $full_name;
                $result["no_payment_method"][$count_no_payment_method]["amount"] = $payroll['amount'];
                $count_no_payment_method++;
                if ($payroll['bonus_extra'] > 0) {
                    $result["no_payment_method"][$count_no_payment_method]["full_name"] = $full_name;
                    $result["no_payment_method"][$count_no_payment_method]["amount"] = $payroll['bonus_extra'];
                    $count_no_payment_method++;
                }
            } elseif ($pay->user->bank_account_id == 2 || $pay->user->bank_account_id == 9 || $pay->user->bank_account_id == 11 || $pay->user->bank_account_id == 17) {
                $result["grupo_aval"][$count_grupo_aval]["email"] =  $pay->user->email;
                $result["grupo_aval"][$count_grupo_aval]["document_number"] = $bank_account_document_number;
                if ($bank_account_document_id == 1)
                    $bank_account_document_id = "CEDULA CIUDADANIA";
                if ($bank_account_document_id == 2)
                    $bank_account_document_id = "CEDULA EXTRANJERIA";
                if ($bank_account_document_id == 3)
                    $bank_account_document_id = "NIT";
                if ($bank_account_document_id == 4)
                    $bank_account_document_id = "PASAPORTE";
                $result["grupo_aval"][$count_grupo_aval]["document_type"] = $bank_account_document_id;
                $result["grupo_aval"][$count_grupo_aval]["full_name"] = $full_name;
                $result["grupo_aval"][$count_grupo_aval]["bank"] = ($pay->user->bank_account_id == 2) ? "AVVILLAS" : $pay->user->bank->name;
                // $result["grupo_aval"][$count_grupo_aval]["bank_account_type"] = ($pay->user->bank_account_type == 1) ? "AHORROS" : "CORRIENTE";
                // $result["grupo_aval"][$count_grupo_aval]["bank_account_type"] = ($pay->user->bank_account_type == strcasecmp("Ahorros") == 0) ? "AHORROS" : "CORRIENTE";
                $bank_account_type = strcasecmp($pay->user->bank_account_type, "Ahorros") == 0 ? "AHORROS" : "CORRIENTE"; // Tipo de cuenta, valida el nombre sin importar si el string esta en mayuscula o minuscula.
                $result["grupo_aval"][$count_grupo_aval]["bank_account_type"] = $bank_account_type;

                $result["grupo_aval"][$count_grupo_aval]["bank_account_number"] =  $pay->user->bank_account_number;
                $result["grupo_aval"][$count_grupo_aval]["email_empty"] =  "";
                $result["grupo_aval"][$count_grupo_aval]["amount"] =  round($payroll['amount'], 0);
                $count_grupo_aval++;

                if ($payroll['bonus_extra'] > 0) {
                    $result["grupo_aval"][$count_grupo_aval]["email"] =  $pay->user->email;
                    $result["grupo_aval"][$count_grupo_aval]["document_number"] = $bank_account_document_number;
                    $result["grupo_aval"][$count_grupo_aval]["document_type"] = $bank_account_document_id;
                    $result["grupo_aval"][$count_grupo_aval]["full_name"] = $full_name;
                    $result["grupo_aval"][$count_grupo_aval]["bank"] = ($pay->user->has_bank_account == 2) ? "AVVILLAS" : $pay->user->bank->name;
                    //$result["grupo_aval"][$count_grupo_aval]["bank_account_type"] = ($pay->user->bank_account_type == 1) ? "AHORROS" : "CORRIENTE";//
                    $bank_account_type = strcasecmp($pay->user->bank_account_type, "Ahorros") == 0 ? "AHORROS" : "CORRIENTE"; // Tipo de cuenta, valida el nombre sin importar si el string esta en mayuscula o minuscula.
                    $result["grupo_aval"][$count_grupo_aval]["bank_account_type"] = $bank_account_type;

                    $result["grupo_aval"][$count_grupo_aval]["bank_account_number"] =  $pay->user->bank_account_number;
                    $result["grupo_aval"][$count_grupo_aval]["email_empty"] =  "";
                    $result["grupo_aval"][$count_grupo_aval]["amount"] = round($payroll['bonus_extra'], 0);
                    $count_grupo_aval++;
                }
            } else {
                $result["pab"][$count_pab]["document_id"] = $bank_account_document_id;
                $result["pab"][$count_pab]["document_number"] = trim($bank_account_document_number);
                $result["pab"][$count_pab]["full_name"] = $full_name;
                $result["pab"][$count_pab]["transaction_type"] = 37;
                $result["pab"][$count_pab]["bank_code"] = $pay->user->bank->code;
                $result["pab"][$count_pab]["bank_account_number"] = trim($pay->user->bank_account_number);
                $result["pab"][$count_pab]["email"] = "";
                $result["pab"][$count_pab]["authorized_document"] = "";

                $increment = explode("L", $reference);
                $increment = $increment[1] + 1;
                $count = strlen($increment);
                $count = 6 - $count;
                for ($i = 0; $i < $count; $i++) {
                    $increment = "0" . $increment;
                }
                $reference = "L" . $increment;
                $result["pab"][$count_pab]["reference"] = $reference;
                $result["pab"][$count_pab]["office"] = "";
                $result["pab"][$count_pab]["amount"] = round($payroll['amount'], 0);
                $result["pab"][$count_pab]["date"] = date("Y") . "" . date("m") . "" . date("d");
                $count_pab++;

                if ($payroll['bonus_extra'] > 0) {
                    $result["pab"][$count_pab]["document_id"] = $bank_account_document_id;
                    $result["pab"][$count_pab]["document_number"] = $bank_account_document_number;
                    $result["pab"][$count_pab]["full_name"] = $full_name;
                    $result["pab"][$count_pab]["transaction_type"] = 37;
                    $result["pab"][$count_pab]["bank_code"] = $pay->user->bank->code;
                    $result["pab"][$count_pab]["bank_account_number"] = $pay->user->bank_account_number;
                    $result["pab"][$count_pab]["email"] = "";
                    $result["pab"][$count_pab]["authorized_document"] = "";

                    $increment = explode("L", $reference);
                    $increment = $increment[1] + 1;
                    $count = strlen($increment);
                    $count = 6 - $count;
                    for ($i = 0; $i < $count; $i++) {
                        $increment = "0" . $increment;
                    }
                    $reference = "L" . $increment;
                    $result["pab"][$count_pab]["reference"] = $reference;
                    $result["pab"][$count_pab]["office"] = "";
                    $result["pab"][$count_pab]["amount"] = $payroll['bonus_extra'];
                    $result["pab"][$count_pab]["date"] = date("Y") . "" . date("m") . "" . date("d");
                    $count_pab++;
                }
            }
        }

        return Excel::download(new PABFormat($result, $range), 'Formato PAB(' . $range . ').xlsx');
    }

    public function pabExportInactive(Request $request)
    {
        $result = [];
        $quarter = ($request->quarter == 0 || ($request->quarter != 1 && $request->quarter != 2)) ? ((date("d") <= 15) ? 1 : 2)  : $request->quarter;
        $month = ($request->month != 0 && ($request->month >= 1 && $request->month <= 12)) ?  $request->month : date("m");
        $year = ($request->year != 0 && ($request->year >= 2015 && $request->year <= 2060)) ?  $request->year : date("Y");

        $date_mask = ($quarter == 1) ? $year . "-" . $month . "-07" : $year . "-" . $month . "-27";
        $range = ($quarter == 1) ? "01-15 " . Carbon::parse($date_mask, 'UTC')
            ->isoFormat('MMMM YYYY') : "16-" . cal_days_in_month(CAL_GREGORIAN, $month, $year) . " " . Carbon::parse($date_mask, 'UTC')->isoFormat('MMMM YYYY');

        //$users = User::with(['role'])->where('status', 1)->where('setting_role_id', '!=', 14)->orderBy('setting_role_id', 'ASC')->get();
        $payrolls = Payroll::where('month', $month)->where('year', $year)->get();

        $reference = "L000000";
        $count_pab = 0;
        $count_grupo_aval = 0;
        $count_no_payment_method = 0;
        $result["pab"] = [];
        $result["grupo_aval"] = [];
        $result["no_payment_method"] = [];

        foreach ($payrolls as $pay) {

            $full_name = substr(($pay->user->first_name . " " . $pay->user->last_name . " " . $pay->user->second_last_name), 0, 30);
            $full_name = str_replace("ñ", "n", $full_name);
            $bank_account_document_id = $pay->user->bank_account_document_id;
            $bank_account_document_number = $pay->user->bank_account_document_number;
            $requestObj = new Request(array(
                "user_id" => $pay->user_id, "quarter" => $quarter,
                "month" => $month, "year" => $year
            ));
            $payroll = $this->getPayroll($requestObj);

            if ($pay->user->has_bank_account == 0) {
                $result["no_payment_method"][$count_no_payment_method]["full_name"] = $full_name;
                $result["no_payment_method"][$count_no_payment_method]["amount"] = $payroll['amount'];
                $count_no_payment_method++;
                if ($payroll['bonus_extra'] > 0) {
                    $result["no_payment_method"][$count_no_payment_method]["full_name"] = $full_name;
                    $result["no_payment_method"][$count_no_payment_method]["amount"] = $payroll['bonus_extra'];
                    $count_no_payment_method++;
                }
            } elseif ($pay->user->bank_account_id == 2 || $pay->user->bank_account_id == 9 || $pay->user->bank_account_id == 11 || $pay->user->bank_account_id == 17) {
                $result["grupo_aval"][$count_grupo_aval]["email"] =  $pay->user->email;
                $result["grupo_aval"][$count_grupo_aval]["document_number"] = $bank_account_document_number;
                if ($bank_account_document_id == 1)
                    $bank_account_document_id = "CEDULA CIUDADANIA";
                if ($bank_account_document_id == 2)
                    $bank_account_document_id = "CEDULA EXTRANJERIA";
                if ($bank_account_document_id == 3)
                    $bank_account_document_id = "NIT";
                if ($bank_account_document_id == 4)
                    $bank_account_document_id = "PASAPORTE";
                $result["grupo_aval"][$count_grupo_aval]["document_type"] = $bank_account_document_id;
                $result["grupo_aval"][$count_grupo_aval]["full_name"] = $full_name;
                $result["grupo_aval"][$count_grupo_aval]["bank"] = ($pay->user->bank_account_id == 2) ? "AVVILLAS" : $pay->user->bank->name;
                $result["grupo_aval"][$count_grupo_aval]["bank_account_type"] = ($pay->user->bank_account_type == 1) ? "AHORROS" : "CORRIENTE";
                $result["grupo_aval"][$count_grupo_aval]["bank_account_number"] =  $pay->user->bank_account_number;
                $result["grupo_aval"][$count_grupo_aval]["email_empty"] =  "";
                $result["grupo_aval"][$count_grupo_aval]["amount"] =  $payroll['amount'];
                $count_grupo_aval++;

                if ($payroll['bonus_extra'] > 0) {
                    $result["grupo_aval"][$count_grupo_aval]["email"] =  $pay->user->email;
                    $result["grupo_aval"][$count_grupo_aval]["document_number"] = $bank_account_document_number;
                    $result["grupo_aval"][$count_grupo_aval]["document_type"] = $bank_account_document_id;
                    $result["grupo_aval"][$count_grupo_aval]["full_name"] = $full_name;
                    $result["grupo_aval"][$count_grupo_aval]["bank"] = ($pay->user->has_bank_account == 2) ? "AVVILLAS" : $pay->user->bank->name;
                    $result["grupo_aval"][$count_grupo_aval]["bank_account_type"] = ($pay->user->bank_account_type == 1) ? "AHORROS" : "CORRIENTE";
                    $result["grupo_aval"][$count_grupo_aval]["bank_account_number"] =  $pay->user->bank_account_number;
                    $result["grupo_aval"][$count_grupo_aval]["email_empty"] =  "";
                    $result["grupo_aval"][$count_grupo_aval]["amount"] =  $payroll['bonus_extra'];
                    $count_grupo_aval++;
                }
            } else {
                $result["pab"][$count_pab]["document_id"] = $bank_account_document_id;
                $result["pab"][$count_pab]["document_number"] = trim($bank_account_document_number);
                $result["pab"][$count_pab]["full_name"] = $full_name;
                $result["pab"][$count_pab]["transaction_type"] = 37;
                $result["pab"][$count_pab]["bank_code"] = $pay->user->bank->code;
                $result["pab"][$count_pab]["bank_account_number"] = trim($pay->user->bank_account_number);
                $result["pab"][$count_pab]["email"] = "";
                $result["pab"][$count_pab]["authorized_document"] = "";

                $increment = explode("L", $reference);
                $increment = $increment[1] + 1;
                $count = strlen($increment);
                $count = 6 - $count;
                for ($i = 0; $i < $count; $i++) {
                    $increment = "0" . $increment;
                }
                $reference = "L" . $increment;
                $result["pab"][$count_pab]["reference"] = $reference;
                $result["pab"][$count_pab]["office"] = "";
                $result["pab"][$count_pab]["amount"] = $payroll['amount'];
                $result["pab"][$count_pab]["date"] = date("Y") . "" . date("m") . "" . date("d");
                $count_pab++;

                if ($payroll['bonus_extra'] > 0) {
                    $result["pab"][$count_pab]["document_id"] = $bank_account_document_id;
                    $result["pab"][$count_pab]["document_number"] = $bank_account_document_number;
                    $result["pab"][$count_pab]["full_name"] = $full_name;
                    $result["pab"][$count_pab]["transaction_type"] = 37;
                    $result["pab"][$count_pab]["bank_code"] = $pay->user->bank->code;
                    $result["pab"][$count_pab]["bank_account_number"] = $pay->user->bank_account_number;
                    $result["pab"][$count_pab]["email"] = "";
                    $result["pab"][$count_pab]["authorized_document"] = "";

                    $increment = explode("L", $reference);
                    $increment = $increment[1] + 1;
                    $count = strlen($increment);
                    $count = 6 - $count;
                    for ($i = 0; $i < $count; $i++) {
                        $increment = "0" . $increment;
                    }
                    $reference = "L" . $increment;
                    $result["pab"][$count_pab]["reference"] = $reference;
                    $result["pab"][$count_pab]["office"] = "";
                    $result["pab"][$count_pab]["amount"] = $payroll['bonus_extra'];
                    $result["pab"][$count_pab]["date"] = date("Y") . "" . date("m") . "" . date("d");
                    $count_pab++;
                }
            }
        }

        return Excel::download(new PABFormat($result, $range), 'Formato PAB(' . $range . ').xlsx');
    }

    public function pdfPayroll()
    {
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('GBMEDIA');
        $pdf->SetTitle('Documento Soporte');
        $pdf->SetSubject('Documento Soporte');
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_RIGHT);
        $pdf->SetAutoPageBreak(TRUE, 0);
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
        if (@file_exists(dirname(__FILE__) . '/lang/eng.php')) {
            require_once(dirname(__FILE__) . '/lang/eng.php');
            $pdf->setLanguageArray($l);
        }
    }

    public function saveMovements(Request $request)
    {
        $this->validate(
            $request,
            [
                'movements_amount' => 'required|numeric',
                'comment' => 'required'
            ],
            [
                'movements_amount.required' => 'Este campo es obligatorio',
                'comment.required' => 'Este campo es obligatorio',
                'movements_amount.numeric' => 'Este campo debe ser numérico',
            ]
        );

        try {
            DB::beginTransaction();

            $for_date = ($request->quarter == 1) ? "07 00:00:00" : "27 00:00:00";
            $for_date = $request->year . "-" . $request->month . "-" . $for_date;

            $movements = new PayrollMovement;
            $movements->user_id = $request->user_id;
            $movements->payroll_type_id = $request->payroll_type_id;
            $movements->amount = $request->movements_amount;
            $movements->created_by = Auth::user()->id;
            $movements->comment = $request->comment;
            $movements->for_date = $for_date;
            $movements->save();

            $requestObj = new Request(array("user_id" => $request->user_id, "quarter" => $request->quarter, "month" => $request->month, "year" => $request->year,));
            $payroll = $this->getPayroll($requestObj);

            DB::commit();
            return response()->json(['success' => true, 'payroll' => $payroll]);
        } catch (Exception $e) {
            DB::rollback();

            return response()->json(['success' => false]);
        }
    }

    public function storeBoutiqueInstallment(Request $request)
    {
        try {
            DB::beginTransaction();

            $result = [
                'success' => true,
                'bigger'  => false,
                'terminated'  => false,
                'permission' => true,
            ];
            $this->validate(
                $request,
                [
                    'amount_installment' => 'required|numeric',
                ],
                [
                    'amount_installment.required' => 'Este campo es obligatorio',
                    'amount_installment.numeric' => 'Este campo debe ser numerico',
                ]
            );

            $payroll_boutique = PayrollBoutique::find($request->id);
            $amount = $payroll_boutique->amount;
            $sum = PayrollBoutiqueInstallment::where('payroll_boutique_id', $payroll_boutique->id)->sum('installment');
            $amount_due = $amount - $sum;
            if ($amount_due < $request->amount_installment) {
                $result = [
                    'success' => false,
                    'bigger'  => true
                ];

                return response()->json($result);
            }

            if (!Auth::user()->can('payroll-boutique-edit')) {
                $result = [
                    'success' => false,
                    'permission'  => false
                ];
                return response()->json($result);
            }

            if ($amount_due == $request->amount_installment) {
                $payroll_boutique->status = 1;
                $payroll_boutique->save();
                $result['terminated'] = true;
            }

            $boutique_installment = new PayrollBoutiqueInstallment;
            $boutique_installment->payroll_boutique_id = $payroll_boutique->id;
            $boutique_installment->installment = $request->amount_installment;
            $boutique_installment->created_by = Auth::user()->id;
            $boutique_installment->save();


            $for_date = (date("d") < 15) ? date("Y") . "-" . date("m") . "-07 00:00:00" : date("Y") . "-" . date("m") . "-27 00:00:00";
            if (date("d") == 15)
                $for_date = date("Y") . "-" . date("m") . "-27 00:00:00";
            if (date("d") == cal_days_in_month(CAL_GREGORIAN, date("m"), date("Y")))
                $for_date = date("Y") . "-" . date("m") . "-07 00:00:00";

            $movements = new PayrollMovement;
            $movements->user_id = $payroll_boutique->user_id;
            $movements->payroll_type_id = 11;
            $movements->amount = $request->amount_installment;
            $movements->created_by = Auth::user()->id;
            $movements->comment = "Abonado el " . date("Y-m-d H:i");
            $movements->for_date = $for_date;
            $movements->save();

            $result['payroll'] = $payroll_boutique;
            DB::commit();
            return response()->json($result);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json(['success' => false]);
        }
    }

    public function updateWorkedDays(Request $request)
    {
        $this->validate(
            $request,
            [
                'worked_days' => 'required|numeric',
            ],
            [
                'worked_days.required' => 'Este campo es obligatorio',
                'worked_days.numeric' => 'Este campo debe ser numérico',
            ]
        );

        try {
            DB::beginTransaction();
            $worked_day = "worked_days" . $request->quarter;
            Payroll::where('user_id', $request->user_id)->where('month', $request->month)->where('year', $request->year)->update([$worked_day => $request->worked_days]);

            $this->calculateUserPayroll($request->user_id);

            $user = User::where('id', $request->user_id)->first();
            if ($user->contract_id == 2) {
                $configuration = RHExtraValue::first();
                $transportation = $configuration->transportation_aid;
                $transportation = $transportation / 2;
                $transportation = $transportation / 15;
                $transportation = $transportation * $request->worked_days;
                $for_date = ($request->quarter == 1) ? "07 00:00:00" : "27 00:00:00";
                $for_date = $request->year . "-" . $request->month . "-" . $for_date;
                $payroll_movements = PayrollMovement::where('user_id', $request->user_id)->where('payroll_type_id', 6)->where('for_date', $for_date)->first();
                $payroll_movements->amount = $transportation;
                $payroll_movements->save();
            }


            $requestObj = new Request(array("user_id" => $request->user_id, "quarter" => $request->quarter, "month" => $request->month, "year" => $request->year,));
            $payroll = $this->getPayroll($requestObj);

            DB::commit();
            return response()->json(['success' => true, 'payroll' => $payroll]);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json(['success' => false]);
        }
    }

    public function viewPayrolls()
    {
        /*$migrations = DB::connection('external')->table('messages')->get();
        dd($migrations);*/

        $user_permissions = Auth()->user()->getPermissionsViaRoles()->pluck('name');

        return view("adminModules.payroll.main.list")->with(compact(['user_permissions']));
    }

    public function viewPayrollBoutique()
    {
        return view("adminModules.payroll.boutique.list");
    }

    public function conectingToOtherDB()
    {
        $migrations = DB::connection('external')->table('migrations')->get();
        dd($migrations);
    }

    public function usersPayrollsExecute()
    {
        try {
            $min_id = 1;
            $max_id = 1100;

            $payrolls_users = DB::connection('gbmedia')->table('nomina')->select('id_usuario')->distinct()->whereBetween('id_usuario', [$min_id, $max_id])->orderBy('id_usuario')->get();

            foreach ($payrolls_users as $payrolls_user) {
                $user = User::select('id')->where('old_user_id', $payrolls_user->id_usuario)->first();

                if (is_null($user)) {
                    continue;
                }

                $user_id = $user->id;

                $user_payrolls = DB::connection('gbmedia')->table('nomina')->where('id_usuario', $payrolls_user->id_usuario)->get();

                foreach ($user_payrolls as $payroll) {
                    DB::beginTransaction();

                    $month = $payroll->mes;
                    $year = $payroll->year;
                    $salary = !empty($payroll->salario_mensual) ? $payroll->salario_mensual : 0;
                    $worked_days = $payroll->cant_dias;
                    $social_security = !empty($payroll->seguridad_social) ? $payroll->seguridad_social : 0;
                    $quarter = $payroll->quincena;

                    $created_payroll = Payroll::firstOrCreate(
                        [
                            'user_id' => $user_id,
                            'month' => $month,
                            'year' => $year,
                        ],
                        [
                            'user_id' => $user_id,
                            'month' => $month,
                            'year' => $year,
                            'salary1' => 0,
                            'worked_days1' => 0,
                            'salary2' => 0,
                            'worked_days2' => 0,
                        ]
                    );

                    if ($quarter == '1-15') {
                        $created_payroll->user_id = $user_id;
                        $created_payroll->month = $month;
                        $created_payroll->year = $year;
                        $created_payroll->salary1 = $salary;
                        $created_payroll->worked_days1 = $worked_days;
                        $created_payroll->salary2 = 0;
                        $created_payroll->worked_days2 = 0;

                        $extra_hour_for_date = "$year-$month-07";
                        $extra_hour_start_date = "$year-$month-01";
                        $extra_hour_end_date = "$year-$month-15";
                    } else {
                        $created_payroll->salary2 = $salary;
                        $created_payroll->worked_days2 = $worked_days;

                        $extra_hour_for_date = "$year-$month-27";
                        $extra_hour_start_date = "$year-$month-16";
                        $extra_hour_end_date = "$year-$month-31";
                    }

                    $created_payroll->save();

                    // Social security amount
                    $created_payroll_movement = PayrollMovement::firstOrCreate(
                        [
                            'user_id' => $user_id,
                            'created_by' => 3,
                            'payroll_type_id' => 12,
                            'amount' => $social_security,
                            'for_date' => $extra_hour_for_date,
                        ],
                        [
                            'user_id' => $user_id,
                            'created_by' => 3,
                            'payroll_type_id' => 12,
                            'amount' => $social_security,
                            'for_date' => $extra_hour_for_date,
                            'comment' => "Seguridad Social",
                        ]
                    );

                    $created_payroll_movement->user_id = $user_id;
                    $created_payroll_movement->created_by = 3;
                    $created_payroll_movement->payroll_type_id = 12;
                    $created_payroll_movement->amount = $social_security;
                    $created_payroll_movement->for_date = $extra_hour_for_date;
                    $created_payroll_movement->comment = "Seguridad Social";
                    $created_payroll_movement->automatic = 1;
                    $created_payroll_movement->created_at = null;
                    $created_payroll_movement->updated_at = null;
                    $created_payroll_movement->save();

                    //Create Payroll Extra hours movement
                    $user_extra_hours = RHExtraHours::where('user_id', $user_id)->where('state_id', 2)->whereBetween('review_date', [$extra_hour_start_date, $extra_hour_end_date])->get();

                    foreach ($user_extra_hours as $user_extra_hour) {
                        if (is_null($user_extra_hour->total) || empty($user_extra_hour->total)) {
                            continue;
                        }

                        $created_payroll_movement = PayrollMovement::firstOrCreate(
                            [
                                'user_id' => $user_extra_hour->user_id,
                                'created_by' => $user_extra_hour->user_acep_den_id,
                                'payroll_type_id' => 14,
                                'amount' => $user_extra_hour->total,
                                'for_date' => $extra_hour_for_date,
                            ],
                            [
                                'user_id' => $user_extra_hour->user_id,
                                'created_by' => $user_extra_hour->user_acep_den_id,
                                'payroll_type_id' => 14,
                                'amount' => $user_extra_hour->total,
                                'comment' => "Hora extra realizada el dia $user_extra_hour->application_date con un valor de: $" . $user_extra_hour->total,
                                'for_date' => $extra_hour_for_date,
                            ]
                        );

                        $created_payroll_movement->user_id = $user_extra_hour->user_id;
                        $created_payroll_movement->created_by = $user_extra_hour->user_acep_den_id;
                        $created_payroll_movement->payroll_type_id = 14;
                        $created_payroll_movement->amount = $user_extra_hour->total;
                        $created_payroll_movement->comment = "Hora extra realizada el dia $user_extra_hour->application_date con un valor de: $" . $user_extra_hour->total;
                        $created_payroll_movement->for_date = $extra_hour_for_date;
                        $created_payroll_movement->automatic = 1;
                        $created_payroll_movement->created_at = null;
                        $created_payroll_movement->updated_at = null;
                        $created_payroll_movement->save();
                    }

                    DB::commit();
                }
            }

            return response()->json(['success' => true]);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json(['success' => false]);
        }
    }

    public function usersPayrollMovementsExecute()
    {
        /*try {
            $GB_types = [
                1 => 1,
                2 => 2,
                3 => 3,
                4 => 4,
                5 => 5,
                6 => 6,
                7 => 7,
                8 => 8,
                9 => 9,
                10 => 10,
                11 => 11,
                12 => 13,
                13 => 12,
            ];

            $min_id = 1;
            $max_id = 5000;

            if (!Schema::hasColumn('payroll_movements', 'old_payroll_movement_id'))
            {
                Schema::table('payroll_movements', function (Blueprint $table) {
                    $table->integer('old_payroll_movement_id')->nullable();
                });
            }

            $payrolls_movements = DB::connection('gbmedia')->table('nomina_dev_des')->whereBetween('nds_id', [$min_id, $max_id])->get();

            foreach ($payrolls_movements AS $movement) {
                $user = User::select('id')->where('old_user_id', $movement->nds_fk_u_id)->first();
                $user_id = $user->id;

                $user_creator = User::select('id')->where('old_user_id', $movement->nds_cobra_u_id)->first();
                $user_creator_id = $user_creator->id;

                $payroll_tpe_id = $GB_types[$movement->nds_tipo];
                $amount = $movement->nds_valor;
                $comment = $movement->nds_observacion;

                $date = $movement->nds_fecha;

                $day = Carbon::parse($movement->nds_fecha)->day;
                $last_day_of_month = Carbon::parse($date)->endOfMonth()->day;

                if (($day >= 1 && $day <= 14) || ($day == $last_day_of_month)) {
                    $for_date = Carbon::parse($date)->year . "-" . Carbon::parse($date)->month . "-07";

                    if ($date == $last_day_of_month) {
                        $date = Carbon::parse($date)->addDay();
                        $for_date = $date->year . "-" . $date->month . "-07";
                    }
                } else {
                    $for_date = Carbon::parse($date)->year . "-" . Carbon::parse($date)->month . "-27";
                }

                $automatic = strpos($comment, '(Automatico)') !== false ? 1 : 0;

                $created_payroll_movement = PayrollMovement::firstOrCreate(
                    [
                        'old_payroll_movement_id' => $movement->nds_id,
                    ],
                    [
                        'user_id' => $user_id,
                        'created_by' => $user_creator_id,
                        'payroll_type_id' => $payroll_tpe_id,
                        'amount' => $amount,
                        'comment' => $comment,
                        'for_date' => $for_date,
                        'created_at' => null,
                        'updated_at' => null,
                    ]
                );

                $created_payroll_movement->user_id = $user_id;
                $created_payroll_movement->created_by = $user_creator_id;
                $created_payroll_movement->payroll_type_id = $payroll_tpe_id;
                $created_payroll_movement->amount = $amount;
                $created_payroll_movement->comment = $comment;
                $created_payroll_movement->for_date = $for_date;
                $created_payroll_movement->automatic = $automatic;
                $created_payroll_movement->created_at = null;
                $created_payroll_movement->updated_at = null;
                $created_payroll_movement->old_payroll_movement_id = $movement->nds_id;
                $created_payroll_movement->save();
            }

            return response()->json(['success' => true]);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json(['success' => false]);
        }*/
    }

    /*public function scriptPayrollBoutique()
    {
        try {
            DB::beginTransaction();
            $payrolls_boutique = DB::connection('gbmedia')->table('nomina_ded_boutique')->get();

            foreach ($payrolls_boutique AS $pb)
            {
                $user = User::select('id')->where('old_user_id', $pb->ndb_fk_u_id)->first();
                $payroll_boutique = new PayrollBoutique();
                $payroll_boutique->old_id = $pb->ndb_id;
                $payroll_boutique->user_id = $user->id;
                $payroll_boutique->amount = $pb->ndb_valor_inicial;
                $payroll_boutique->installment = $pb->ndb_desea_pagar;
                $payroll_boutique->status = $pb->ndb_estado;
                $payroll_boutique->comment = $pb->ndb_detalle;
                $payroll_boutique->created_at = $pb->ndb_fecha_inicio." 00:00:00";
                $payroll_boutique->save();

                $nomina_abono = DB::connection('gbmedia')->table('nomina_abono_ded_bout')->where('nadb_fk_ndb_id', $pb->ndb_id)->get();
                foreach ($nomina_abono as $nom_ab)
                {
                    $payroll_boutique_installment = new PayrollBoutiqueInstallment;
                    $payroll_boutique_installment->payroll_boutique_id = $payroll_boutique->id;
                    $payroll_boutique_installment->installment = $nom_ab->nadb_valor;
                    $user = User::select('id')->where('old_user_id', $nom_ab->nadb_modificado_por)->first();
                    $payroll_boutique_installment->created_by = $user->id;
                    $payroll_boutique_installment->created_at = $nom_ab->nadb_fecha. " 00:00:00";
                    $payroll_boutique_installment->save();
                }


            }

            DB::commit();
            return response()->json(['success' => true]);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json(['success' => false]);
        }
    }*/
}
