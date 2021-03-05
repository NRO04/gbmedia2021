<?php

namespace App\Http\Controllers\Satellite;

use App\Exports\Satellite\EarningsSheet;
use App\Exports\Satellite\NoPaymentMethodsSheet;
use App\Exports\Satellite\PayrollAcumulated;
use App\Exports\Satellite\PayrollSheet;
use App\Exports\Satellite\PayrollStatistic;
use App\Exports\Satellite\SiigoSheet;
use App\Exports\Satellite\WithAVVillas;
use App\Exports\Satellite\WithBancolombia;
use App\Exports\Satellite\WithBancoomeva;
use App\Http\Controllers\Controller;
use App\Mail\Satellite\ActivatedAccount;
use App\Mail\Satellite\OwnerStatistic;
use App\Mail\Satellite\OwnerStatistics2;
use App\Models\Boutique\BoutiqueBlockedUser;
use App\Models\Globals\City;
use App\Models\Globals\GlobalCountry;
use App\Models\Satellite\SatelliteApi;
use App\Models\Satellite\SatelliteContract;
use App\Models\Satellite\SatelliteProspect;
use App\Models\Satellite\SatelliteTemplateStatistic;
use App\Models\Tenancy\Tenant;
use DateTime;
use Elibyy\TCPDF\Facades\TCPDF;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Auth;
use DataTables;
use App\Traits\TraitGlobal;
use App\Models\Satellite\SatelliteAccount;
use App\Models\Satellite\SatelliteAccountLog;
use App\Models\Satellite\SatelliteAccountNote;
use App\Models\Satellite\SatelliteAccountPartner;
use App\Models\Satellite\SatelliteAccountStatus;
use App\Models\Satellite\SatelliteOwner;
use App\Models\Satellite\SatelliteOwnerDocumentation;
use App\Models\Satellite\SatelliteOwnerPaymentInfo;
use App\Models\Satellite\SatelliteOwnerCommissionRelation;
use App\Models\Satellite\SatelliteUser;
use App\Models\Satellite\SatelliteUsersImage;
use App\Models\Satellite\SatelliteUsersDocumentsType;
use App\Models\Satellite\SatelliteTemplatesType;
use App\Models\Satellite\SatelliteTemplatesForEmail;
use App\Models\Satellite\SatelliteTemplatesPagesField;
use App\Models\Satellite\SatellitePaymentMethod;
use App\Models\Satellite\SatellitePaymentPage;
use App\Models\Satellite\SatellitePaymentFile;
use App\Models\Satellite\SatellitePaymentAccount;
use App\Models\Satellite\SatellitePaymentCommission;
use App\Models\Satellite\SatellitePaymentDeduction;
use App\Models\Satellite\SatellitePaymentPayDeduction;
use App\Models\Satellite\SatellitePaymentPayroll;
use App\Models\Settings\SettingPage;
use App\Models\Globals\Bank;
use App\Models\Globals\Document;
use App\Models\Globals\Department;
use Carbon\Carbon;
use App\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use App\Mail\Satellite\CreatedAccount;
use App\Events\PaymentCommission;
use App\Events\Satellite\Payment\PaymentAccount;
use App\Imports\Satellite\Payment\PaymentExcel;
use App\Imports\Satellite\Payment\PaymentCSV;
use App\Imports\Satellite\Payment\PaymentCSVSkyprivate;
use App\Imports\Satellite\Payment\PaymentText;
use Maatwebsite\Excel\Facades\Excel;
use mysqli;
use PDF;

class SatelliteController extends Controller
{
    use TraitGlobal;

    public function acumulatePayment(Request $request)
    {
        try {
            DB::beginTransaction();

            SatellitePaymentAccount::where("payroll_id", $request->payroll_id)->update(["payroll_id" => null]);
            SatellitePaymentCommission::where("payroll_id", $request->payroll_id)->update(["payroll_id" => null]);

            $paydeductions = SatellitePaymentPayDeduction::where("payroll_id", $request->payroll_id)->get();

            foreach ($paydeductions as $paydeduction) {
                $deduction_id = $paydeduction->deduction_id;
                $deduction = SatellitePaymentDeduction::find($deduction_id);
                $deduction->amount = $deduction->amount + $paydeduction->amount;
                $deduction->times_paid = $deduction->times_paid - 1;
                $deduction->status = 0;
                $deduction->finished_date = null;
                $last_pay = SatellitePaymentPayDeduction::select('payment_date')->where("payroll_id", '!=', $request->payroll_id)->where("owner_id",  $request->owner_id)->where("deduction_id",  $deduction_id)->orderBy('payment_date', 'desc')->first();
                $deduction->last_pay = ($last_pay != null)? $last_pay->payment_date : null;
                $deduction->save();

                SatellitePaymentPayDeduction::where('id', $paydeduction->id)->delete();
            }

            SatellitePaymentPayroll::where('id', $request->payroll_id)->delete();

            DB::commit();
            return response()->json(["success" => true]);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json(["success" => false]);
        }
    }

    public function activatingAccount(Request $request)
    {
        $account = SatelliteAccount::find($request->account_id);
        $original = $account->getOriginal();
        $account->status_id = 2;
        $account->comment = "";
        $account->save();

        $log = new SatelliteAccountLog;
        $log->type = "Estado";
        $log->account_id = $account->id;
        $log->action = "modificado";
        $log->previous = "Vetado";
        $log->now = "Activo";
        $log->created_by = Auth::user()->id;
        $log->save();

        $log = new SatelliteAccountLog;
        $log->type = "Comentario";
        $log->account_id = $account->id;
        $log->action = "modificado";
        $log->previous = $original["comment"];
        $log->now = "";
        $log->created_by = Auth::user()->id;
        $log->save();
    }

    public function assignCommission(Request $request)
    {
        try {
            DB::beginTransaction();
            $commission = SatellitePaymentCommission::find($request->commission_id);
            $payroll = SatellitePaymentPayroll::with(['globalBank', 'globalDocument', 'paymentMethods'])->get()->find($request->payroll_id);

            $percent = $payroll->percent;
            $trm = $payroll->trm;
            $transaction = $payroll->transaction;
            $payment_method = $payroll->payment_methods_id;

            if ($commission->assign_to == 0) {

                $total = $payroll->total + $commission->amount;
                $percent_gb = round(($total * (100 - $percent) / 100), 2);
                $percent_studio = $total - $percent_gb;

                $retention = 0;

                if ($payment_method == 2 || $payment_method == 3) {
                    $retention = round( $trm * ($percent_studio * 4 / 100));
                }

                $payment = round($percent_studio * $trm) - $transaction - $retention;
                $payroll->total = $total;
                $payroll->percent_gb = $percent_gb;
                $payroll->percent_gb_pesos = round($percent_gb * $trm);
                $payroll->percent_studio = $percent_studio;
                $payroll->retention = $retention;
                $payroll->payment = $payment;
                $payroll->save();
                $commission->payroll_id = $payroll->id;
                $commission->save();
            }

            if ($commission->assign_to == 1) {

                $percent_studio = $payroll->percent_studio + $commission->amount;
                $retention = 0;

                if ($payment_method == 2 || $payment_method == 3) {
                    $retention = round( $trm * ($percent_studio * 4 / 100));
                }

                $payment = round($percent_studio * $trm) - $transaction - $retention;
                $payroll->percent_studio = $percent_studio;
                $payroll->retention = $retention;
                $payroll->payment = $payment;
                $payroll->save();
                $commission->payroll_id = $payroll->id;
                $commission->save();

            }

            if ($commission->assign_to == 2) {

                $payment = $payroll->payment + $commission->amount;
                $payroll->payment = $payment;
                $payroll->save();
                $commission->payroll_id = $payroll->id;
                $commission->save();
            }

            $request2 = new Request([
                'owner_id'   => $payroll->owner_id,
                'payment_date'  => $payroll->payment_date,
            ]);
            $this->calculatePayroll($request2);

            DB::commit();
            return response()->json(["success" => true, 'commission' => $commission , 'payroll' => $payroll]);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json(["success" => false]);
        }
    }

    public function assignMassiveCommission(Request $request)
    {
        try {
            DB::beginTransaction();

            $commissions = $request->not_assigned_commissions;
            $payroll = SatellitePaymentPayroll::with(['globalBank', 'globalDocument', 'paymentMethods'])->get()->find($request->payroll_id);
            $massive = [];
            foreach ($commissions as $data)
            {
                if (array_key_exists('massive', $data))
                {
                    if ($data['massive'])
                    {
                        $commission = SatellitePaymentCommission::find($data['id']);
                        $percent = $payroll->percent;
                        $trm = $payroll->trm;
                        $transaction = $payroll->transaction;
                        $payment_method = $payroll->payment_methods_id;

                        if ($commission->assign_to == 0) {

                            $total = $payroll->total + $commission->amount;
                            $percent_gb = round(($total * (100 - $percent) / 100), 2);
                            $percent_studio = $total - $percent_gb;

                            $retention = 0;

                            if ($payment_method == 2 || $payment_method == 3) {
                                $retention = round( $trm * ($percent_studio * 4 / 100));
                            }

                            $payment = round($percent_studio * $trm) - $transaction - $retention;
                            $payroll->total = $total;
                            $payroll->percent_gb = $percent_gb;
                            $payroll->percent_gb_pesos = round($percent_gb * $trm);
                            $payroll->percent_studio = $percent_studio;
                            $payroll->retention = $retention;
                            $payroll->payment = $payment;
                            $payroll->save();
                            $commission->payroll_id = $payroll->id;
                            $commission->save();
                        }

                        if ($commission->assign_to == 1) {

                            $percent_studio = $payroll->percent_studio + $commission->amount;
                            $retention = 0;

                            if ($payment_method == 2 || $payment_method == 3) {
                                $retention = round( $trm * ($percent_studio * 4 / 100));
                            }

                            $payment = round($percent_studio * $trm) - $transaction - $retention;
                            $payroll->percent_studio = $percent_studio;
                            $payroll->retention = $retention;
                            $payroll->payment = $payment;
                            $payroll->save();
                            $commission->payroll_id = $payroll->id;
                            $commission->save();
                        }

                        if ($commission->assign_to == 2) {

                            $payment = $payroll->payment + $commission->amount;
                            $payroll->payment = $payment;
                            $payroll->save();
                            $commission->payroll_id = $payroll->id;
                            $commission->save();
                        }

                        $massive[] = $commission;
                    }
                }
            }
            $request2 = new Request([
                'owner_id'   => $payroll->owner_id,
                'payment_date'  => $payroll->payment_date,
            ]);
            $this->calculatePayroll($request2);
            DB::commit();
            return response()->json(["success" => true, 'payroll' => $payroll, 'massive' => $massive]);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json(["success" => false]);
        }
    }

    public function blockBoutique(Request $request)
    {
        /*dd($request);*/
        try {
            DB::beginTransaction();
            if ($request->status == "checked"){
                $block = new BoutiqueBlockedUser;
                $block->user_id = $request->user_id;
                $block->blocked_by_user_id = Auth::user()->id;
                $block->save();
            }
            else{
                BoutiqueBlockedUser::where('user_id', $request->user_id)->delete();
            }

            DB::commit();
            return response()->json(['success' => true]);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json(['success' => false]);
        }
    }

    public function createOwner()
    {
        $departments = DB::table('global_departments')->select('id', 'name')->orderBy('name', 'asc')->get();
        return view("adminModules.satellite.owner.create")->with([
            "departments" => $departments,
        ]);
    }

    public function createUser()
    {
        $documents = SatelliteUsersDocumentsType::select('id', 'name')->get();
        $countries = DB::table('global_countries')->select('id', 'name', 'code')->get();
        return view("adminModules.satellite.user.create")->with([
            "documents" => $documents,
            "countries" => $countries,
        ]);
    }

    public function createAccount()
    {
        $documents = SatelliteUsersDocumentsType::select('id', 'name')->get();
        $countries = DB::table('global_countries')->select('id', 'name', 'code')->get();
        $pages = DB::table('satellite_templates_pages_fields')->where('template_type_id', 1)->get();
        return view("adminModules.satellite.account.create")->with([
            "documents" => $documents,
            "countries" => $countries,
            "pages" => $pages,
        ]);
    }

    public function createPayment()
    {
        $pages = DB::table('satellite_templates_pages_fields')->where('template_type_id', 1)->get();
        return view("adminModules.satellite.payment.create")->with([
            "pages" => $pages,
        ]);
    }

    public function configTemplate($id)
    {
        $status = SatelliteTemplatesType::find($id);
        if ($status == null)
        {
            return redirect('satellite/template/list');
        }
        $pages = SatelliteTemplatesPagesField::select('satellite_templates_pages_fields.*', 'for_email.subject', 'for_email.body', 'users.first_name', 'users.last_name' )->leftJoin('satellite_templates_for_emails as for_email', 'satellite_templates_pages_fields.id', 'for_email.template_page_id')->leftJoin('users', 'for_email.user_id', 'users.id')->where('satellite_templates_pages_fields.template_type_id', $id)->get();


        return view("adminModules.satellite.template.config")->with([
            "status" => $status,
            "pages" => $pages,
        ]);
    }

    public function coincidenceBanned(Request $request)
    {
        $result = [
            'email' => false,
            'document_number' => false,
            'payment_document_number' => false,
            'phone' => false,
            'payment_phone' => false,
            'others_emails' => false,
            'statistics_emails' => false,
            'address' => false,
            'payment_address' => false,
            'holder' => false,
            'account_number' => false,

        ];
        $owner = SatelliteOwner::find($request->owner_id);
        $owners_banned = SatelliteOwner::where('status',2)->get();

        $others_emails = explode("," , $owner->others_emails);
        $statistics_emails = explode("," , $owner->statistics_emails);

        foreach ($owners_banned as $key => $banned) {

            if (($banned->document_number == $owner->document_number || $banned->paymentInfo->document_number == $owner->document_number) && $owner->document_number != "")
                $result['document_number'] = true;
            if (($banned->document_number == $owner->paymentInfo->document_number || $banned->paymentInfo->document_number == $owner->paymentInfo->document_number) && $owner->paymentInfo->document_number != "")
                $result['payment_document_number'] = true;

            if (($banned->address == $owner->address || $banned->paymentInfo->address == $owner->address) && $owner->address != "")
                $result['address'] = true;
            if (($banned->address == $owner->paymentInfo->address || $banned->paymentInfo->address == $owner->paymentInfo->address) && $owner->paymentInfo->address != "")
                $result['payment_address'] = true;

            if (($banned->phone == $owner->phone || $banned->paymentInfo->phone == $owner->phone) && $owner->phone != "")
                $result['phone'] = true;
            if (($banned->phone == $owner->paymentInfo->phone || $banned->paymentInfo->phone == $owner->paymentInfo->phone) && $owner->paymentInfo->phone != "")
                $result['payment_phone'] = true;

            if ($banned->paymentInfo->holder == $owner->paymentInfo->holder && $owner->paymentInfo->holder != "")
                $result['holder'] = true;

            if ($banned->paymentInfo->account_number == $owner->paymentInfo->account_number && $owner->paymentInfo->account_number != "")
                $result['account_number'] = true;

            for ($j=0; $j < count($others_emails) ; $j++)
            {
                if (trim($banned->email) == trim($others_emails[$j]) && trim($others_emails[$j]) != "") {
                    $result['others_emails'] = true;
                    break;
                }
            }

            for ($j=0; $j < count($statistics_emails) ; $j++)
            {
                if (trim($banned->email) == trim($statistics_emails[$j]) && trim($statistics_emails[$j]) != "") {
                    $result['statistics_emails'] = true;
                    break;
                }
            }

            $banned_others_emails = explode("," , $banned->others_emails);
            for ($i=0; $i < count($banned_others_emails) ; $i++)
            {
                if (trim($banned_others_emails[$i]) == $owner->email) {
                    $result['email'] = true;
                }

                for ($j=0; $j < count($others_emails) ; $j++)
                {
                    if (trim($banned_others_emails[$i]) == trim($others_emails[$j]) && trim($others_emails[$j]) != "") {
                        $result['others_emails'] = true;
                    }
                }

                for ($j=0; $j < count($statistics_emails) ; $j++)
                {
                    if (trim($banned_others_emails[$i]) == trim($statistics_emails[$j]) && trim($statistics_emails[$j]) != "") {
                        $result['others_emails'] = true;
                    }
                }
            }

            $banned_statistics_emails = explode("," , $banned->statistics_emails);
            for ($i=0; $i < count($banned_statistics_emails) ; $i++)
            {
                if (trim($banned_statistics_emails[$i]) == $owner->email) {
                    $result['email'] = true;
                }

                for ($j=0; $j < count($others_emails) ; $j++)
                {
                    if (trim($banned_statistics_emails[$i]) == trim($others_emails[$j]) && trim($others_emails[$j]) != "") {
                        $result['others_emails'] = true;
                    }
                }

                for ($j=0; $j < count($statistics_emails) ; $j++)
                {
                    if (trim($banned_statistics_emails[$i]) == trim($statistics_emails[$j]) && trim($statistics_emails[$j]) != "") {
                        $result['others_emails'] = true;
                    }
                }
            }


        }



        return $result;
    }

    public function createAllCommission(Request $request)
    {
        $receivers = SatelliteOwnerCommissionRelation::select('owner_receiver')->distinct('owner_receiver')->get();
        $total_rows = count($receivers);
        $count = 0;
        foreach ($receivers as $receiver) {
            $givers = SatelliteOwnerCommissionRelation::select('owner_giver')->where('owner_receiver', $receiver->owner_receiver)->distinct('owner_giver')->get();
            foreach ($givers as $giver){
                $this->createCommisionForReceiver($receiver->owner_receiver, $giver->owner_giver, $request->payment_date);
            }
            $count++;
            $verified_commissions["percent"] = ($count * 100 ) / ($total_rows);
            //event(new PaymentCommission($verified_commissions));
        }

        if ($count == 0)
        {
            $verified_commissions["percent"] = 100;
            //event(new PaymentCommission($verified_commissions));
        }

        return response()->json(['success' => true]);
    }

    public function createCommisionForReceiver($owner_receiver, $owner_giver, $payment_date)
    {
        $exists = SatellitePaymentAccount::where('owner_id', $owner_giver)->where('payment_date', $payment_date)->exists();
        $commissions_relation = SatelliteOwnerCommissionRelation::where('owner_giver', $owner_giver)->where('owner_receiver', $owner_receiver)->get();

        if ($exists == true && count($commissions_relation) > 0) {
            $amount = 0;
            foreach ($commissions_relation as $commission_relation) {

                if ($commission_relation->type == 1) {
                    $amount += SatellitePaymentAccount::where('owner_id', $owner_giver)->where('payment_date', $payment_date)->sum('amount');
                }
                if ($commission_relation->type == 2) {
                    $amount += SatellitePaymentAccount::where('owner_id', $owner_giver)->where('payment_date', $payment_date)
                    ->where('page_id', $commission_relation->page)
                    ->sum('amount');
                }
                if ($commission_relation->type == 3) {
                    $amount += SatellitePaymentAccount::where('owner_id', $owner_giver)->where('payment_date', $payment_date)
                    ->where('page_id', '!=' ,$commission_relation->page)
                    ->sum('amount');
                }

                $amount = ($amount * $commission_relation->percent) / 100;
            }


            $amount = round($amount, 2);

            if ($amount > 0) {

                $owner = SatelliteOwner::select('owner')->where('id', $owner_giver)->get();
                $description = "Comision de : ".$owner[0]->owner." de fecha de pago: ".$payment_date;
                $commission = SatellitePaymentCommission::where('owner_id', $owner_receiver)
                ->where('coming_from', $owner_giver)
                ->where('payment_date', $payment_date)
                ->first();

                if ($commission != null) {
                    $commission->amount = $amount;
                    $commission->save();
                }
                else{
                    $commission = new SatellitePaymentCommission;
                    $commission->payment_date = $payment_date;
                    $commission->owner_id = $owner_receiver;
                    $commission->assign_to = 1;
                    $commission->amount = $amount;
                    $commission->description = $description;
                    $commission->coming_from = $owner_giver;
                    $commission->created_by = Auth::user()->id;
                    $commission->save();
                }

            }
        }
    }

    public function createCommission(Request $request)
    {
        $this->validate($request,
        [
            'c_amount' => 'required|numeric',
            'c_description' => 'required',
        ],
        [
            'c_amount.required' => 'Este campo es obligatorio',
            'c_amount.numeric' => 'Este campo debe ser numerico',
            'c_description.required' => 'Este campo es obligatorio',
        ]);

        try {
            DB::beginTransaction();
            $commission = new SatellitePaymentCommission;
            $commission->owner_id = $request->owner_id;
            $commission->assign_to = $request->c_assign_to;
            $commission->amount = $request->c_amount;
            $commission->description = $request->c_description;
            $commission->created_by = Auth::user()->id;
            $commission->save();

            DB::commit();
            return response()->json(["success" => true, "commission" => $commission]);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json(["success" => false]);
        }
    }

    public function createDeduction(Request $request)
    {
        $this->validate($request,
        [
            'd_amount' => 'required|numeric',
            'd_description' => 'required',
        ],
        [
            'd_amount.required' => 'Este campo es obligatorio',
            'd_amount.numeric' => 'Este campo debe ser numerico',
            'd_description.required' => 'Este campo es obligatorio',
        ]);

        try {
            DB::beginTransaction();
            $deduction = new SatellitePaymentDeduction;
            $deduction->owner_id = $request->owner_id;
            $deduction->deduction_to = $request->d_deduction_to;
            $deduction->total = $request->d_amount;
            $deduction->amount = $request->d_amount;
            $deduction->description = $request->d_description;
            $deduction->created_by = Auth::user()->id;
            $deduction->save();

            DB::commit();
            return response()->json(["success" => true, "deduction" => $deduction]);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json(["success" => false]);
        }
    }

    public function createPayDeduction(Request $request)
    {
        $this->validate($request,
        [
            'pd_amount' => 'required|numeric',
            'payroll_id' => 'required',
        ],
        [
            'pd_amount.required' => 'Este campo es obligatorio',
            'pd_amount.numeric' => 'Este campo debe ser numerico',
            'payroll_id.required' => 'Este campo es obligatorio',
        ]);

        try {
            DB::beginTransaction();

            $deduction = SatellitePaymentDeduction::find($request->deduction_id);
            if ($deduction->amount < $request->pd_amount) {
                return response()->json([
                    "success" => false,
                    "can_pay" => false,
                ]);
            }

            $payroll = SatellitePaymentPayroll::with(['globalBank', 'globalDocument', 'paymentMethods'])->get()->find($request->payroll_id);

            $percent = $payroll->percent;
            $trm = $payroll->trm;
            $transaction = $payroll->transaction;
            $payment_method = $payroll->payment_methods_id;

            if ($deduction->deduction_to == 0) {

                $total = $payroll->total - $request->pd_amount;
                if ($total < 0) {
                    return response()->json([
                        "success" => false,
                        "can_pay" => true,
                        "can_pay_payroll" => false,
                    ]);
                }
                else
                {
                    $percent_gb = round(($total * (100 - $percent) / 100), 2);
                    $percent_studio = $total - $percent_gb;
                    $retention = 0;

                    if ($total > 0)
                    {
                        if ($payment_method == 1 || $payment_method == 2 || $payment_method == 3) {
                            $retention = round( $trm * ($percent_studio * 4 / 100));
                        }

                        $payment = round($percent_studio * $trm) - $transaction - $retention;
                    }
                    else
                    {
                        $payment = 0;
                        $transaction = 0;
                    }

                    $payroll->total = $total;
                    $payroll->percent_gb = $percent_gb;
                    $payroll->percent_studio = $percent_studio;
                    $payroll->retention = $retention;
                    $payroll->transaction = $transaction;
                    $payroll->payment = $payment;
                    $payroll->save();
                }
            }

            if ($deduction->deduction_to == 1) {

                $percent_studio = $payroll->percent_studio - $request->pd_amount;
                if ($percent_studio < 0) {
                    return response()->json([
                        "success" => false,
                        "can_pay" => true,
                        "can_pay_payroll" => false,
                    ]);
                }
                else{

                    $retention = 0;
                    if ($percent_studio > 0)
                    {
                        if ($payment_method == 1 || $payment_method == 2 || $payment_method == 3) {
                            $retention = round( $trm * ($percent_studio * 4 / 100));
                        }

                        $payment = round($percent_studio * $trm) - $transaction - $retention;
                    }
                else
                    {
                        $payment = 0;
                        $transaction = 0;
                    }

                    $payroll->percent_studio = $percent_studio;
                    $payroll->retention = $retention;
                    $payroll->transaction = $transaction;
                    $payroll->payment = $payment;
                    $payroll->save();

                }
            }

            if ($deduction->deduction_to == 2) {

                $payment = $payroll->payment - $request->pd_amount;
                if ($payment < 0) {
                    return response()->json([
                        "success" => false,
                        "can_pay" => true,
                        "can_pay_payroll" => false,
                    ]);
                }
                else{
                    $payroll->payment = $payment;
                    $payroll->save();
                }
            }

            if (($deduction->amount - $request->pd_amount) == 0) {
                $deduction->status = 1;
                $deduction->finished_date = $request->payment_date;
            }
            $deduction->amount = $deduction->amount - $request->pd_amount;
            if ($deduction->payment_date == null) {
                $deduction->payment_date = $request->payment_date;
            }

            $deduction->last_pay = $request->payment_date;
            $deduction->times_paid += 1;
            $deduction->save();

            $paydeduction = new SatellitePaymentPayDeduction;
            $paydeduction->payment_date = $request->payment_date;
            $paydeduction->owner_id = $request->owner_id;
            $paydeduction->deduction_id = $request->deduction_id;
            $paydeduction->payroll_id = $request->payroll_id;
            $paydeduction->amount = $request->pd_amount;
            $paydeduction->created_by = Auth::user()->id;
            $paydeduction->save();

            $paydeduction->user = $paydeduction->created_by_user->userFullName();

            $request2 = new Request([
                'owner_id'   => $request->owner_id,
                'payment_date'  => $request->payment_date,
            ]);
            $this->calculatePayroll($request2);
            DB::commit();
            return response()->json([
                "success" => true,
                "can_pay" => true,
                "paydeduction" => $paydeduction,
                "deduction" => $deduction,
                "payment_payroll" => $payroll,
            ]);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json(["success" => false]);
        }
    }

    public function calculatePayroll(Request $request)
    {
        if($request->payment_date == "")
        {
            return false;
        }
        $payroll = SatellitePaymentPayroll::where('owner_id', $request->owner_id)->where('payment_date', $request->payment_date)->first();

        $paydeductions = SatellitePaymentPayDeduction::select('satellite_payment_paydeductions.*')
            ->join('satellite_payment_deductions', 'satellite_payment_paydeductions.deduction_id', 'satellite_payment_deductions.id' )
            ->where('satellite_payment_paydeductions.owner_id', $request->owner_id)
            ->where('satellite_payment_paydeductions.payment_date', $request->payment_date)
            ->orderBy('satellite_payment_deductions.deduction_to', 'ASC')->get();
        $res_total = 0;
        $res_percent = 0;
        $res_payment = 0;
        foreach ($paydeductions as $paydeduction)
        {
            $amount = $paydeduction->amount;
            $deduction = SatellitePaymentDeduction::where('id', $paydeduction->deduction_id)->first();

            if ($deduction->deduction_to == 0) {
                $res_total = $res_total + $amount;
            }

            if ($deduction->deduction_to == 1) {
                $res_percent = $res_percent + $amount;
            }

            if ($deduction->deduction_to == 2) {
                $res_payment = $res_payment + $amount;
            }
            $paydeduction->payroll_id = $payroll->id;
            $paydeduction->save();
        }

        $commissions = SatellitePaymentCommission::where('payroll_id', $payroll->id)->orderBy('assign_to', 'ASC')->get();
        $sum_total = 0;
        $sum_percent = 0;
        $sum_payment = 0;
        foreach ($commissions as $commission)
        {
            $amount = $commission->amount;
            if ($commission->assign_to == 0) {
                $sum_total = $sum_total + $amount;

            }

            if ($commission->assign_to == 1) {
                $sum_percent = $sum_percent + $amount;
            }

            if ($commission->assign_to == 2) {
                $sum_payment = $sum_payment + $amount;
            }

            $commission->payroll_id = $payroll->id;
            $commission->save();
        }

        $total = $payroll->original_total + $sum_total - $res_total;
        $percent_gb = round(($total * (100 - $payroll->percent) / 100), 2);
        $percent_studio = $total - $percent_gb + $sum_percent - $res_percent;

        $retention = 0;

        if ($payroll->payment_methods_id == 1 || $payroll->payment_methods_id == 2 || $payroll->payment_methods_id == 3) {
            $retention = round($payroll->trm * ($percent_studio * 4 / 100));
        }

        $payment = round($percent_studio * $payroll->trm) - $payroll->transaction - $retention;
        $payroll->total = $total;
        $payroll->percent_gb = $percent_gb;
        $payroll->percent_studio = $percent_studio;
        $payroll->retention = $retention;
        $payroll->payment = $payment + $sum_payment - $res_payment;
        $payroll->save();
    }

    public function createPayroll(Request $request)
    {
        try {
            DB::beginTransaction();

            $amount = SatellitePaymentAccount::where('owner_id', $request->owner_id)->where('payroll_id', null)->sum('amount');
            $owner = SatelliteOwner::find($request->owner_id);

            $file = SatellitePaymentFile::select('payment_date', 'trm')->orderBy('payment_date', 'desc')->first();
            $exists = SatellitePaymentPayroll::where('owner_id', $request->owner_id)->where('payment_date', $file->payment_date)->exists();
            if ($exists) {
                return response()->json(["success" => false, "exists" => true, "trm_null" => false]);
            }
            if ($file != null) {
                if ($file->trm == null) {
                    return response()->json(["success" => false, "exists" => false, "trm_null" => true]);
                }
                $payroll = new SatellitePaymentPayroll;
                $payroll->owner_id = $owner->id;
                $payroll->is_user = $owner->is_user;
                $payroll->payment_date = $file->payment_date;
                $first_date = SatellitePaymentFile::select('start_date')->where('payment_date', $file->payment_date)->orderBy('start_date', 'ASC')->first();
                $last_date = SatellitePaymentFile::select('end_date')->where('payment_date', $file->payment_date)->orderBy('end_date', 'DESC')->first();
                $payroll->payment_range = $first_date->start_date." al ".$last_date->end_date;
                $payroll->total = $amount;
                $payroll->original_total = $amount;
                $payroll->percent = $owner->commission_percent;

                $percent_gb = round(($amount * (100 - $owner->commission_percent) / 100), 2);
                $percent_studio = $amount - $percent_gb;
                $payroll->percent_studio = $percent_studio;
                $payroll->percent_gb = $percent_gb;

                $trm_value = SatellitePaymentFile::select('trm')->where('payment_date', $file->payment_date)->first();
                $payroll->trm = $trm_value->trm;

                $payroll->percent_gb_pesos = round($percent_gb * $trm_value->trm);
                $payment_method = $owner->payment_method;
                $transaction = 0;
                $retention = 0;

                if ($payment_method == 1 || $payment_method == 2 || $payment_method == 3 || $payment_method == 5 || $payment_method == 6 || $payment_method == 8 || $payment_method == 9) {
                    $transaction = 3570;
                }

                if ($payment_method == 1 || $payment_method == 2 || $payment_method == 3) {
                    $retention = round($trm_value->trm * ($percent_studio * 4 / 100));
                }

                $payroll->transaction = $transaction;
                $payroll->retention = $retention;

                $payroll->payment = round($percent_studio * $trm_value->trm) - $transaction - $retention;

                $owner_payment_info = SatelliteOwnerPaymentInfo::where('owner', $request->owner_id)->get();

                $payroll->payment_methods_id = $owner->payment_method;
                $payroll->holder = $owner_payment_info[0]->holder;
                $payroll->bank = $owner_payment_info[0]->bank;
                $payroll->bank_usa = $owner_payment_info[0]->bank_usa;
                $payroll->document_type = $owner_payment_info[0]->document_type;
                $payroll->document_number = $owner_payment_info[0]->document_number;
                $payroll->account_type = $owner_payment_info[0]->account_type;
                $payroll->account_number = $owner_payment_info[0]->account_number;
                $payroll->city_id = $owner_payment_info[0]->city_id;
                $payroll->address = $owner_payment_info[0]->address;
                $payroll->phone = $owner_payment_info[0]->phone;
                $payroll->country = $owner_payment_info[0]->country;
                $payroll->created_by = Auth::user()->id;
                $payroll->rut = SatelliteOwnerDocumentation::where('owner', $owner->id)->where('type', 1)->exists();
                $last_pay = SatellitePaymentPayroll::where('owner_id', $owner->id)->where('payment_date','!=' ,$file->payment_date)->orderBy('payment_date', 'DESC')->first();

                if ($last_pay != null)
                {
                    $first_time = 0;
                    if ($last_pay->payment_methods_id != $owner->payment_method){
                        $first_time = 1;
                    }
                    if ($last_pay->holder != $owner_payment_info[0]->holder){
                        $first_time = 1;
                    }
                    if ($last_pay->bank != $owner_payment_info[0]->bank){
                        $first_time = 1;
                    }
                    if ($last_pay->account_number != $owner_payment_info[0]->account_number){
                        $first_time = 1;
                    }
                    if ($last_pay->document_number != $owner_payment_info[0]->document_number){
                        $first_time = 1;
                    }
                    $payroll->first_time = $first_time;
                }
                else{
                    $payroll->first_time = 1;
                }
                $payroll->save();

                SatellitePaymentAccount::where('owner_id', $owner->id)->where('payroll_id', null)->update(['payroll_id' => $payroll->id]);
            }
            else
            {
                return response()->json(["success" => false, "exists" => false, "trm_null" => false]);
            }


            DB::commit();
            return response()->json(["success" => true, "payment_date" => $file->payment_date]);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json(["success" => false]);
        }
    }

    public function changeTRM(Request $request)
    {
        try {
            DB::beginTransaction();

            SatellitePaymentFile::where('payment_date', $request->payment_date)->update(['trm' => $request->trm_value]);
            $payment_owners = SatellitePaymentAccount::select('owner_id')->distinct('owner_id')->where('payroll_id', null)->where('payment_date', $request->payment_date)->get();
            $first_date = SatellitePaymentFile::select('start_date')->where('payment_date', $request->payment_date)->orderBy('start_date', 'ASC')->first();
            $last_date = SatellitePaymentFile::select('end_date')->where('payment_date', $request->payment_date)->orderBy('end_date', 'DESC')->first();

            foreach ($payment_owners as $payment_owner) {

                $amount = SatellitePaymentAccount::where('owner_id', $payment_owner->owner_id)->where('payroll_id', null)->sum('amount');

                //solo se le genera pago a los propietarios que no estan vetados
                $owner = SatelliteOwner::where('id', $payment_owner->owner_id)->where('status', 1)->first();
                if ($owner == null)
                {
                    continue;
                }

                //usar esta linea si el propietario tiene que tener forma de pago para que se le pague
                /*if (($amount >= 65 && $owner->is_user == 0 && $owner->payment_method != 1) || ($amount >= 1 && $owner->is_user == 1 && $owner->payment_method != 1))*/
                if (($amount >= 65 && $owner->is_user == 0) || ($amount >= 1 && $owner->is_user == 1)) {

                    $payroll = new SatellitePaymentPayroll;
                    $payroll->owner_id = $owner->id;
                    $payroll->is_user = $owner->is_user;
                    $payroll->payment_date = $request->payment_date;
                    $payroll->payment_range = $first_date->start_date." al ".$last_date->end_date;
                    $payroll->total = $amount;
                    $payroll->original_total = $amount;
                    $payroll->percent = $owner->commission_percent;

                    $percent_gb = round(($amount * (100 - $owner->commission_percent) / 100), 2);
                    $percent_studio = $amount - $percent_gb;
                    $payroll->percent_studio = $percent_studio;
                    $payroll->percent_gb = $percent_gb;
                    $payroll->percent_gb_pesos = round($percent_gb * $request->trm_value);
                    $payroll->trm = $request->trm_value;

                    $payment_method = $owner->payment_method;
                    $transaction = 0;
                    $retention = 0;

                    if ($payment_method == 1 || $payment_method == 2 || $payment_method == 3 || $payment_method == 5 || $payment_method == 6 || $payment_method == 8 ||
                    $payment_method == 9) {
                        $transaction = 3570;
                    }

                    if ($payment_method == 1 || $payment_method == 2 || $payment_method == 3) {
                        $retention = round($request->trm_value * ($percent_studio * 4 / 100));
                    }

                    $payroll->transaction = $transaction;
                    $payroll->retention = $retention;

                    $payroll->payment = round($percent_studio * $request->trm_value) - $transaction - $retention;

                    $owner_payment_info = SatelliteOwnerPaymentInfo::where('owner', $payment_owner->owner_id)->first();
                    if ($owner_payment_info == null)
                    {
                        $owner_payment_info = SatelliteOwnerPaymentInfo::create([
                            'owner' => $owner->id
                        ]);
                    }
                    $payroll->payment_methods_id = $owner->payment_method;
                    $payroll->holder = $owner_payment_info->holder;
                    $payroll->bank = $owner_payment_info->bank;
                    $payroll->bank_usa = $owner_payment_info->bank_usa;
                    $payroll->document_type = $owner_payment_info->document_type;
                    $payroll->document_number = $owner_payment_info->document_number;
                    $payroll->account_type = ($owner_payment_info->account_type == null)? 0 : $owner_payment_info->account_type;
                    $payroll->account_number = $owner_payment_info->account_number;
                    $payroll->city_id = $owner_payment_info->city_id;
                    $payroll->address = $owner_payment_info->address;
                    $payroll->phone = $owner_payment_info->phone;
                    $payroll->country = $owner_payment_info->country;
                    $payroll->created_by = Auth::user()->id;
                    $payroll->rut = SatelliteOwnerDocumentation::where('owner', $owner->id)->where('type', 1)->exists();
                    $last_pay = SatellitePaymentPayroll::where('owner_id', $owner->id)->where('payment_date','!=' ,$request->payment_date)
                        ->orderBy('payment_date', 'DESC')->first();
                    if ($last_pay != null)
                    {
                        $first_time = 0;
                        if ($last_pay->payment_methods_id != $owner->payment_method){
                            $first_time = 1;
                        }
                        if ($last_pay->holder != $owner_payment_info->holder){
                            $first_time = 1;
                        }
                        if ($last_pay->bank != $owner_payment_info->bank){
                            $first_time = 1;
                        }
                        if ($last_pay->account_number != $owner_payment_info->account_number){
                            $first_time = 1;
                        }
                        if ($last_pay->document_number != $owner_payment_info->document_number){
                            $first_time = 1;
                        }
                        $payroll->first_time = $first_time;
                    }
                    else{
                        $payroll->first_time = 1;
                    }
                    $payroll->save();

                    SatellitePaymentAccount::where('owner_id', $owner->id)->where('payroll_id', null)->update(['payroll_id' => $payroll->id]);

                }
            }

            DB::commit();
            return response()->json(['success' => true]);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json(['success' => false]);
        }
    }

    public function editOwner($id)
    {
        $owner = SatelliteOwner::find($id);
        $list_owners = SatelliteOwner::select('id','owner')->where('id', '!=', $id)->get();
        $payment_methods = SatellitePaymentMethod::all();
        if(!is_null($owner)){
            $exists = SatelliteOwnerPaymentInfo::where('owner', $id)->exists();
            if (!$exists){
                SatelliteOwnerPaymentInfo::create([
                    'owner' => $id
                ]);
            }
        }
        $banks = Bank::all();
        $departments = Department::all();
        $documents = Document::where('is_listed', 1)->get();
        $pages = SettingPage::select('id','name')->get();
        $users_support = User::select('id','first_name','last_name','second_last_name')->where('setting_role_id', 4)->get();
        $cities = DB::table('global_cities')->select('id', 'name')->where('department_id',$owner->department_id)->orderBy('name', 'asc')->get();
        $tenants = Tenant::all();
    	return view("adminModules.satellite.owner.edit")->with([
            "owner" => $owner,
            "departments" => $departments,
            "cities" => $cities,
            "payment_methods" => $payment_methods,
            "banks" => $banks,
            "documents" => $documents,
            "pages" => $pages,
            "list_owners" => $list_owners,
            "users_support" => $users_support,
            "tenants" => $tenants,
        ]);
    }

    public function editUser(Request $request)
    {
        $result = SatelliteUser::where('id', $request->id)->get()->toArray();
        $images = SatelliteUsersImage::where('satellite_user_id', $request->id)->get();
        $result_image['front_image'] = "../../images/default/placeholder_front.png";
        $result_image['front_image_size'] = "50%";
        $result_image['front_image_exists'] = false;
        $result_image['back_image'] = "../../images/default/placeholder_back.png";
        $result_image['back_image_size'] = "50%";
        $result_image['back_image_exists'] = false;
        $result_image['holding_image'] = "../../images/default/placeholder_holding.png";
        $result_image['holding_image_size'] = "50%";
        $result_image['holding_image_exists'] = false;
        $result_image['profile_image'] = "../../images/default/placeholder_profile.png";
        $result_image['profile_image_size'] = "50%";
        $result_image['profile_image_exists'] = false;
        foreach ($images as $key => $img) {
            if ($img['type'] == 1)
            {
               $result_image['front_image'] = global_asset("http://gbmediagroup.com/laravel/storage/app/public/".tenant('studio_slug')."/satellite/user/$img->image");
               $result_image['front_image_size'] = "100%";
               $result_image['front_image_exists'] = true;
            }
            if ($img['type'] == 2)
            {
               $result_image['back_image'] = global_asset("http://gbmediagroup.com/laravel/storage/app/public/".tenant('studio_slug')."/satellite/user/$img->image");
               $result_image['back_image_size'] = "100%";
               $result_image['back_image_exists'] = true;
            }
            if ($img['type'] == 3)
            {
               $result_image['holding_image'] = global_asset("http://gbmediagroup.com/laravel/storage/app/public/".tenant('studio_slug')."/satellite/user/$img->image");
               $result_image['holding_image_size'] = "100%";
               $result_image['holding_image_exists'] = true;
            }
            if ($img['type'] == 4)
            {
               $result_image['profile_image'] = global_asset("http://gbmediagroup.com/laravel/storage/app/public/".tenant('studio_slug')."/satellite/user/$img->image");
               $result_image['profile_image_size'] = "100%";
               $result_image['profile_image_exists'] = true;
            }
        }

        $result = array_merge($result[0], $result_image);
        return response()->json($result);
    }

    public function editAccount(Request $request)
    {
        $result = SatelliteAccount::where('id', $request->id)->get();
        $result = $result[0];
        $data = SatelliteAccountPartner::select('id', 'name')->where('account_id', $request->id)->get();
        $partners = [];
        $cont = 0;
        foreach ($data as $value) {
            $partners[$cont]['id'] = $value['id'];
            $partners[$cont]['name'] = $value['name'];
            $partners[$cont]['deleted'] = 0;
            $cont++;
        }
        $result['partners'] = $partners;
        $owner = SatelliteOwner::select('owner')->where('id', $result->owner_id)->get();
        $result['owner'] = $owner[0]->owner;

        return response()->json($result);
    }

    public function existsUser(Request $request)
    {
        $this->validate($request,
        [
            'document_type' => 'required',
            'document_number' => 'required',
        ],
        [
            'document_type.required' => 'Este campo es obligatorio',
            'document_number.required' => 'Este campo es obligatorio',
        ]);

        if (($request->document_type >= 1 && $request->document_type <= 3)) {
            $country = 49;
        }
        else{
            $country = $request->country_id;
        }

        $result = SatelliteUser::where('document_type', $request->document_type)
        ->where('document_number', $request->document_number)
        ->where('country_id', $country)
        ->first();

        return response()->json([
            "exists" => ($result == null)? false : true,
            "user" => $result,
        ]);
    }

    public function exportEarnings(Request $request)
    {
        $earnings = $this->getEarnings($request);
        $earnings = json_decode($earnings->content(), true);
        return Excel::download(new EarningsSheet($earnings), 'Ganancias(' . $request->year . ').xlsx');
    }

    public function exportSiigo(Request $request)
    {
        $explode_payment_date = explode("-", $request->payment_date);
        $sequence = 0;
        $cont = 0;
        $results = SatellitePaymentPayroll::where('payment_date', $request->payment_date)->get();
        foreach ($results as $key => $value)
        {
            if ($value->payment_methods_id == 4)
            {
                continue;
            }
            for ($i = 0; $i < 8; $i++)
            {
                if ($value->owner->is_user == 1 && $i == 3 || $value->owner->is_user == 1 && $i == 4 || $value->owner->is_user == 0 && $i == 5
                    || $value->owner->is_user == 0 && $i == 6)
                {
                    continue;
                }
                $contable_account = 2805050102;
                if ($i == 1)
                    $contable_account = 2365250100;
                if ($i == 2)
                    $contable_account = 4210950100;
                if ($i == 3 || $i == 4)
                    $contable_account = 138095;
                if ($i == 5 || $i == 6)
                    $contable_account = 110505;
                if ($i == 7)
                    $contable_account = 1110050100;

                $debit_credit = "D";
                if ($i == 1 || $i == 2 || $i == 4 || $i == 6 || $i == 7 )
                    $debit_credit = "C";

                if ($i == 0)
                    $column_f = $value->percent_studio * $value->trm;
                if ($i == 1)
                    $column_f = $value->retention;
                if ($i == 2)
                    $column_f = $value->transaction;
                if ($i == 3 || $i == 5)
                    $column_f = SatellitePaymentCommission::where('payroll_id', $value->id)->where('assign_to', 2)->sum('amount');
                if ($i == 4 || $i == 6){
                    $column_f = 0;
                    $paydeductions = SatellitePaymentPayDeduction::where('payroll_id', $value->id)->get();
                    foreach ($paydeductions as $paydeduction)
                    {
                        $exists = SatellitePaymentDeduction::where('id', $paydeduction->deduction_id)->where('deduction_to', 2)->exists();
                        if ($exists)
                        {
                            $column_f = $column_f + $paydeduction->amount;
                        }
                    }
                }
                if ($i == 7)
                    $column_f = $value->payment;

                if(($i == 1 || $i == 2 || $i == 3 || $i == 4 || $i == 5 || $i == 6) && $column_f == 0)
                    continue;

                $sequence++;
                $result[$cont]["A"] = "G";
                $result[$cont]["B"] = 1;
                $result[$cont]["C"] = $value->owner->owner;
                $result[$cont]["D"] = $contable_account;
                $result[$cont]["E"] = $debit_credit;
                $result[$cont]["F"] = $column_f;
                $result[$cont]["G"] = $explode_payment_date[0];
                $result[$cont]["H"] = $explode_payment_date[1];
                $result[$cont]["I"] = $explode_payment_date[2];
                $result[$cont]["J"] = 1;
                $result[$cont]["K"] = 0;
                $result[$cont]["L"] = 0;
                $result[$cont]["M"] = $sequence;
                $result[$cont]["N"] = 6;
                $result[$cont]["O"] = 0;
                $result[$cont]["P"] = ($value->document_type == 3)? substr($value->document_number, 0, 9) : $value->document_number;
                $result[$cont]["Q"] = 0;
                if ($i == 0)
                    $column_r = "INGRESOS RECIBDOS PARA  TERCEROS  S.C. 80%     ";
                if ($i == 1)
                    $column_r = "SERVICIOS 4%";
                if ($i == 2)
                    $column_r = "TRANSACCION";
                if ($i == 3 || $i == 4)
                    $column_r = "OTROS";
                if ($i == 5 || $i == 6)
                    $column_r = "CAJA GENERAL";
                if ($i == 7)
                    $column_r = "BANCO";

                $result[$cont]["R"] = $column_r;
                $result[$cont]["S"] = ($i == 0)? $value->percent_studio * $value->trm : 0;
                $result[$cont]["T"] = "N";
                $result[$cont]["U"] = "";
                $result[$cont]["V"] = ($i == 7)? 10 : 0;
                $result[$cont]["W"] = "0,00";
                $result[$cont]["X"] = "0,00";
                $result[$cont]["Y"] = "0,00";
                $result[$cont]["Z"] = "0,00";
                //
                $result[$cont]["AA"] = "0,00";
                $result[$cont]["AB"] = "";
                $result[$cont]["AC"] = 0;
                $result[$cont]["AD"] = "";
                $result[$cont]["AE"] = "";
                $result[$cont]["AF"] = "0,00";
                $result[$cont]["AG"] = "0,00";
                $result[$cont]["AH"] = ($i == 1)? $value->percent_studio * $value->trm : "";
                $result[$cont]["AI"] = "0,00";
                $result[$cont]["AJ"] = "";
                $result[$cont]["AK"] = "";
                $result[$cont]["AL"] = "";
                $result[$cont]["AM"] = "0,00";
                $result[$cont]["AN"] = ($i == 0)? 20 : "";
                $result[$cont]["AO"] = ($i == 0)? 2 : "";
                $result[$cont]["AP"] = ($i == 1)? 2 : "";
                $result[$cont]["AQ"] = 0;
                $result[$cont]["AR"] = 0;
                $result[$cont]["AS"] = ($i == 0)? 1 : 0;
                $result[$cont]["AT"] = 0;
                $result[$cont]["AU"] = 0;
                $result[$cont]["AV"] = 0;
                $result[$cont]["AW"] = 0;
                $result[$cont]["AX"] = "";
                $result[$cont]["AY"] = "";
                $result[$cont]["AZ"] = "";
                //
                $result[$cont]["BA"] = 0;
                $result[$cont]["BB"] = 0;
                $result[$cont]["BC"] = 0;
                $result[$cont]["BD"] = "";
                $result[$cont]["BE"] = "";
                $result[$cont]["BF"] = "";
                $result[$cont]["BG"] = "";
                $result[$cont]["BH"] = "";
                $result[$cont]["BI"] = 0;
                $result[$cont]["BJ"] = 0;
                $result[$cont]["BK"] = 0;
                $result[$cont]["BL"] = 0;
                $result[$cont]["BM"] = "";
                $result[$cont]["BN"] = 0;
                $result[$cont]["BO"] = 0;
                $result[$cont]["BP"] = "";
                $result[$cont]["BQ"] = "";
                $result[$cont]["BR"] = "";
                $result[$cont]["BS"] = 0;
                $result[$cont]["BT"] = "";
                $result[$cont]["BU"] = "";
                $result[$cont]["BV"] = "";
                $result[$cont]["BW"] = "";
                $result[$cont]["BX"] = "";
                $result[$cont]["BY"] = 0;
                $result[$cont]["BZ"] = 0;
                //
                $result[$cont]["CA"] = 0;
                $result[$cont]["CB"] = 0;
                $result[$cont]["CC"] = 0;
                $result[$cont]["CD"] = 0;
                $result[$cont]["CE"] = 0;
                $result[$cont]["CF"] = "";
                $result[$cont]["CG"] = 0;
                $result[$cont]["CH"] = "";
                $result[$cont]["CI"] = 0;
                $result[$cont]["CJ"] = "";
                $result[$cont]["CK"] = 0;
                $cont++;
            }
        }
        return Excel::download(new SiigoSheet($result), 'Resumen Siigo(' . $request->payment_date . ').xlsx');
    }

    public function exportOwnerPayroll(Request $request)
    {
        $owner = SatelliteOwner::find($request->owner_id);
        $payroll = SatellitePaymentPayroll::where('payment_date', $request->payment_date)->where('owner_id', $request->owner_id)->first();
        if($payroll != null)
        {
            $accounts_send[0] = [];
            $payroll_accounts = SatellitePaymentAccount::where('payroll_id', $payroll->id)->get();

            foreach ($payroll_accounts as $key => $payroll_account)
            {
                $accounts_send[$key]["payment_date"] = $payroll_account->payment_date;
                $accounts_send[$key]["page"] = $payroll_account->page->name;
                $accounts_send[$key]["nick"] = $payroll_account->nick;
                $accounts_send[$key]["amount"] = $payroll_account->amount;
                $accounts_send[$key]["description"] = $payroll_account->description;
            }

            $commission_send[0] = [];
            $commissions = SatellitePaymentCommission::where('payroll_id', $payroll->id)->get();
            foreach ($commissions as $key => $commission)
            {
                $commission_send[$key]["amount"] = $commission->amount;
                $commission_send[$key]["assign_to"] = ($commission->assign_to == 2)? "Pesos" : "Dolares";
                $commission_send[$key]["description"] = $commission->description;
            }

            $deduction_send[0] = [];
            $deductions = SatellitePaymentDeduction::where([
                ['owner_id', $request->owner_id],
                ['payment_date', null]
            ])->orWhere([
                ['owner_id', $request->owner_id],
                ['payment_date', '<=' , $request->payment_date],
                ['finished_date', '>=' , $request->payment_date],
                ['status', 1],
            ])->orWhere([
                ['owner_id', $request->owner_id],
                ['payment_date', '<=' , $request->payment_date],
                ['finished_date', null ],
                ['status', 0],
            ])->get();
            foreach ($deductions as $key => $deduction)
            {
                $deduction_send[$key]["created_at"] = date_format(date_create($deduction->created_at),"d M Y");
                $deduction_send[$key]["total"] = $deduction->total;
                $deduction_send[$key]["times_paid"] = $deduction->times_paid;
                $deduction_send[$key]["deduction_to"] = ($deduction->deduction_to == 2)? "Pesos" : "Dolares";
                $deduction_send[$key]["amount"] = $deduction->amount;
                $deduction_send[$key]["description"] = $deduction->description;
                $deduction_send[$key]["paydeduction"] = "";
                $paydeductions = SatellitePaymentPayDeduction::where('deduction_id', $deduction->id)->get();
                foreach ($paydeductions as $paydeduction)
                {
                    if ($deduction_send[$key]["paydeduction"] == "")
                        $deduction_send[$key]["paydeduction"] = date_format(date_create($paydeduction->payment_date),"d M Y")." ($".$paydeduction->amount.")";
                    else
                        $deduction_send[$key]["paydeduction"] = ", ".date_format(date_create($paydeduction->payment_date),"d M Y")." ($".$paydeduction->amount.")";
                }
            }

            $payroll_send['payroll'][0]["payment_date"] = $payroll->payment_date;
            $payroll_send['payroll'][0]["payment_range"] = $payroll->payment_range;
            $payroll_send['payroll'][0]["total"] = $payroll->total;
            $payroll_send['payroll'][0]["percent_gb"] = $payroll->percent_gb;
            $payroll_send['payroll'][0]["percent_studio"] = $payroll->percent_studio;
            $payroll_send['payroll'][0]["trm"] = $payroll->trm;
            $payroll_send['payroll'][0]["transaction"] = $payroll->transaction;
            $payroll_send['payroll'][0]["retention"] = $payroll->retention;
            $payroll_send['payroll'][0]["payment"] = $payroll->payment;

            $excel['payroll'] = $payroll_send;
            $excel['accounts'] = $accounts_send;
            $excel['commissions'] = $commission_send;
            $excel['deductions'] = $deduction_send;

            return Excel::download(new PayrollStatistic($excel), 'Pago.xlsx');
        }
        else
        {
            $accounts_send[0] = [];
            $payroll_accounts = SatellitePaymentAccount::where('payroll_id', null)->where('owner_id', $request->owner_id)->get();

            foreach ($payroll_accounts as $key => $payroll_account)
            {
                $accounts_send[$key]["payment_date"] = $payroll_account->payment_date;
                $accounts_send[$key]["page"] = $payroll_account->page->name;
                $accounts_send[$key]["nick"] = $payroll_account->nick;
                $accounts_send[$key]["amount"] = $payroll_account->amount;
                $accounts_send[$key]["description"] = $payroll_account->description;
            }

            $commission_send[0] = [];
            $commissions = SatellitePaymentCommission::where('payroll_id', null)->where('owner_id', $request->owner_id)->get();
            foreach ($commissions as $key => $commission)
            {
                $commission_send[$key]["amount"] = $commission->amount;
                $commission_send[$key]["assign_to"] = ($commission->assign_to == 2)? "Pesos" : "Dolares";
                $commission_send[$key]["description"] = $commission->description;
            }

            $deduction_send[0] = [];
            $deductions = SatellitePaymentDeduction::where([
                ['owner_id', $request->owner_id],
                ['payment_date', null]
            ])->orWhere([
                ['owner_id', $request->owner_id],
                ['payment_date', '<=' , $request->payment_date],
                ['finished_date', '>=' , $request->payment_date],
                ['status', 1],
            ])->orWhere([
                ['owner_id', $request->owner_id],
                ['payment_date', '<=' , $request->payment_date],
                ['finished_date', null ],
                ['status', 0],
            ])->get();
            foreach ($deductions as $key => $deduction)
            {
                $deduction_send[$key]["created_at"] = date_format(date_create($deduction->created_at),"d M Y");
                $deduction_send[$key]["total"] = $deduction->total;
                $deduction_send[$key]["times_paid"] = $deduction->times_paid;
                $deduction_send[$key]["deduction_to"] = ($deduction->deduction_to == 2)? "Pesos" : "Dolares";
                $deduction_send[$key]["amount"] = $deduction->amount;
                $deduction_send[$key]["description"] = $deduction->description;
                $deduction_send[$key]["paydeduction"] = "";
                $paydeductions = SatellitePaymentPayDeduction::where('deduction_id', $deduction->id)->get();
                foreach ($paydeductions as $paydeduction)
                {
                    if ($deduction_send[$key]["paydeduction"] == "")
                        $deduction_send[$key]["paydeduction"] = date_format(date_create($paydeduction->payment_date),"d M Y")." ($".$paydeduction->amount.")";
                    else
                        $deduction_send[$key]["paydeduction"] = ", ".date_format(date_create($paydeduction->payment_date),"d M Y")." ($".$paydeduction->amount.")";
                }
            }

            $excel['payroll'] = 0;
            $excel['accounts'] = $accounts_send;
            $excel['commissions'] = $commission_send;
            $excel['deductions'] = $deduction_send;

            return Excel::download(new PayrollAcumulated($excel), 'Pago.xlsx');
        }

    }

    public function getOwners()
    {
        $owners = SatelliteOwner::select('id','owner','first_name', 'second_name', 'last_name', 'second_last_name', 'email', 'commission_percent', 'phone', 'status', 'others_emails', 'statistics_emails')
            ->where('is_user', 0)->orderBy('status', 'ASC')->get();
        $count = SatelliteOwner::where('is_user', 0)->count();
        $cont = 0;
        $result = [];
        foreach($owners as $key => $owner){
            $result[$cont]["id"] = $owner->id;
            $result[$cont]["owner"] = $owner->owner;
            $result[$cont]["percent"] = $owner->commission_percent;
            $result[$cont]["email"] = $owner->email;
            $result[$cont]["phone"] = $owner->phone;
            $result[$cont]["full_name"] = $owner->first_name." ".$owner->second_name." ".$owner->last_name." ".$owner->second_last_name;
            $result[$cont]["accounts"] = SatelliteAccount::where('owner_id', $owner->id)->count();
            $result[$cont]["status"] = $owner->status;
            $result[$cont]["others_emails"] = $owner->others_emails;
            $result[$cont]["statistics_emails"] = $owner->statistics_emails;
            $result[$cont]["status_name"] = ($owner->status == 1)? "activo" : (($owner->status == 2)? "vetado" : "inactivo");
            $result[$cont]["rut"] = SatelliteOwnerDocumentation::where('owner', $owner->id)->where('type', 1)->exists();
            $result[$cont]["chamber_commerce"] = SatelliteOwnerDocumentation::where('owner', $owner->id)->where('type', 2)->exists();
            $result[$cont]["shareholder_structure"] = SatelliteOwnerDocumentation::where('owner', $owner->id)->where('type', 3)->exists();
            $result[$cont]["bank_certification"] = SatelliteOwnerDocumentation::where('owner', $owner->id)->where('type', 4)->exists();
            $cont++;
        }

        return response()->json(["result" => $result, "count" => $count]);
    }

    public function getContracts()
    {
        $original_tenant_id = tenant('id');
        $tenant = Tenant::find(1);

        $contracts = $tenant->run(function () use ($tenant, $original_tenant_id) {
            if ($original_tenant_id == 1)
            {
                return SatelliteContract::all();
            }
            else
            {
                return SatelliteContract::where('from', $original_tenant_id)->get();
            }

        });

        return response()->json($contracts);
    }

    public function getOwnersModels(Request $request)
    {
        if ($request->status_filter == 0)
        {
            $owners = SatelliteOwner::select('id','owner','first_name', 'second_name', 'last_name', 'second_last_name', 'email', 'commission_percent', 'phone', 'status', 'purchase_limit')
                ->where('is_user', 1)->orderBy('status', 'ASC')->get();
        }
        else{
            $owners = SatelliteOwner::select('id','owner','first_name', 'second_name', 'last_name', 'second_last_name', 'email', 'commission_percent', 'phone', 'status', 'purchase_limit')
                ->where('is_user', 1)->where('status', $request->status_filter)->orderBy('status', 'ASC')->get();
        }


        $cont = 0;
        $result = [];
        foreach($owners as $key => $owner){
            $result[$cont]["id"] = $owner->id;
            $result[$cont]["owner"] = $owner->owner;
            $result[$cont]["percent"] = $owner->commission_percent;
            $result[$cont]["email"] = $owner->email;
            $result[$cont]["phone"] = $owner->phone;
            $result[$cont]["full_name"] = $owner->first_name." ".$owner->second_name." ".$owner->last_name." ".$owner->second_last_name;
            $result[$cont]["accounts"] = SatelliteAccount::where('owner_id', $owner->id)->count();
            $result[$cont]["status"] = $owner->status;
            $result[$cont]["status_name"] = ($owner->status == 1)? "activo" : (($owner->status == 2)? "vetado" : "inactivo");
            $result[$cont]["purchase_limit"] = $owner->purchase_limit;
            $cont++;
        }

        return response()->json($result);
    }

    public function getOwnersManaged()
    {
        $owners = SatelliteOwner::select('id','owner','first_name', 'second_name', 'last_name', 'second_last_name', 'email', 'commission_percent', 'phone', 'status', 'user_manager')
            ->where('user_manager', '!=', null)->get();

        $cont = 0;
        $result = [];
        foreach($owners as $key => $owner){
            $result[$cont]["id"] = $owner->id;
            $result[$cont]["owner"] = $owner->owner;
            $result[$cont]["percent"] = $owner->commission_percent;
            $result[$cont]["email"] = $owner->email;
            $result[$cont]["phone"] = $owner->phone;
            $result[$cont]["full_name"] = $owner->first_name." ".$owner->second_name." ".$owner->last_name." ".$owner->second_last_name;
            $result[$cont]["status"] = $owner->status;
            $result[$cont]["manager"] = $owner->manager->first_name." ".$owner->manager->last_name." ".$owner->manager->second_last_name;
            $cont++;
        }

        return response()->json($result);
    }

    public function getEarningPayReceived(Request $request)
    {
        $now = date("Y-m-d");
        $substract = "-".$request->option." month";
        $start_date =  date("Y-m-d", strtotime($substract, strtotime($now)));
        $result = [];
        $owners = SatellitePaymentPayroll::select('owner_id')->where('is_user', 0)->whereBetween('payment_date', [$start_date, $now])->groupBy('owner_id')
            ->orderByRaw('SUM(percent_studio) DESC')->get();
        $cont = 0;
        foreach($owners as $owner){
            $result[$cont]["id"] = $owner->owner_id;
            $result[$cont]["owner"] = $owner->owner->owner;
            $result[$cont]["amount"] = SatellitePaymentPayroll::where('owner_id', $owner->owner_id)
                ->whereBetween('payment_date', [$start_date, $now])->sum('percent_studio');
            $result[$cont]["percent"] = $owner->owner->commission_percent;
            $result[$cont]["email"] = $owner->owner->email;
            $result[$cont]["phone"] = $owner->owner->phone;
            $result[$cont]["full_name"] = $owner->owner->first_name." ".$owner->owner->second_name." ".$owner->owner->last_name." ".$owner->owner->second_last_name;
            $result[$cont]["accounts"] = SatelliteAccount::where('owner_id', $owner->owner_id)->count();
            $result[$cont]["status"] = $owner->owner->status;
            $cont++;
        }

        return response()->json($result);
    }

    public function getEarningsGraphic(Request $request)
    {
        if($request->range == 1)
        {
            $start_date =  $request->year."-".$request->month."-01";
            $end_date =  $request->year."-".$request->month."-".cal_days_in_month(CAL_GREGORIAN, $request->month, $request->year);
        }
        elseif($request->range == 2)
        {
            if ($request->trimester == 1)
            {
                $start_date =  $request->year."-01-01";
                $end_date =  $request->year."-03-31";
            }
            if ($request->trimester == 2)
            {
                $start_date =  $request->year."-04-01";
                $end_date =  $request->year."-06-30";
            }
            if ($request->trimester == 3)
            {
                $start_date =  $request->year."-07-01";
                $end_date =  $request->year."-09-30";
            }
            if ($request->trimester == 4)
            {
                $start_date =  $request->year."-10-01";
                $end_date =  $request->year."-12-31";
            }

        }
        elseif($request->range == 3)
        {
            if ($request->semester == 1)
            {
                $start_date =  $request->year."-01-01";
                $end_date =  $request->year."-06-30";
            }
            if ($request->semester == 2)
            {
                $start_date =  $request->year."-07-01";
                $end_date =  $request->year."-12-31";
            }
        }
        elseif($request->range == 4)
        {
            $start_date =  $request->year."-01-01";
            $end_date =  $request->year."-12-31";
        }
        elseif($request->range == 5)
        {
            $start_date =  $request->start_date;
            $end_date =  $request->end_date;
        }
        $result = [];
        $departments = Department::select('global_departments.id', 'global_departments.name')->join('satellite_owners as so', 'so.department_id', 'global_departments.id')
            ->join('satellite_payment_accounts as spa', 'spa.owner_id', 'so.id')
            ->where('so.is_user', 0)
            ->whereBetween('spa.payment_date', [$start_date, $end_date])->groupBy('global_departments.id')
            ->orderByRaw('SUM(spa.amount) DESC')->get();

        $cont = 0;
        $total = 0;
        $categories = [];
        $series = [];
        $region = [];

        /*$departments = Department::all();*/
        foreach($departments as $department){
            $categories[$cont] = $department->name;
            $series[$cont] = round(SatellitePaymentAccount::join('satellite_owners as so', 'so.id', 'satellite_payment_accounts.owner_id')
                ->where('is_user', 0)->whereBetween('payment_date', [$start_date, $end_date])->where('so.department_id', $department->id)
                ->sum('amount'), 2);
            $total += $series[$cont];
            $region[$department->name]["title"] = SatellitePaymentAccount::select('so.owner', DB::raw("SUM(amount) as total"))
                ->join('satellite_owners as so', 'so.id', 'satellite_payment_accounts.owner_id')
                ->where('is_user', 0)->whereBetween('payment_date', [$start_date, $end_date])
                ->where('so.department_id', $department->id)->groupBy('so.id')->orderByRaw('SUM(satellite_payment_accounts.amount) DESC')->get();
            $cont++;
        }
        $categories[$cont] = "Sin Ciudad";
        $series[$cont] = round(SatellitePaymentAccount::join('satellite_owners as so', 'so.id', 'satellite_payment_accounts.owner_id')
            ->where('is_user', 0)->whereBetween('payment_date', [$start_date, $end_date])->where('so.department_id', null)
            ->sum('amount'), 2);
        $region["Sin Ciudad"]["title"] = SatellitePaymentAccount::select('so.owner', DB::raw("SUM(amount) as total"))
            ->join('satellite_owners as so', 'so.id', 'satellite_payment_accounts.owner_id')
            ->where('is_user', 0)->whereBetween('payment_date', [$start_date, $end_date])
            ->where('so.department_id', null)->groupBy('so.id')->orderByRaw('SUM(satellite_payment_accounts.amount) DESC')->get();

        $result["categories"] = $categories;
        $result["series"] = $series;
        $result["region"] = $region;
        $result["total"] = round($total, 2);
        $result["average"] = ($cont > 0)? round(($total/ $cont), 2) : 0;
        return response()->json($result);
    }

    public function getUsers(Request $request)
    {
        $data = SatelliteUser::select('id','first_name', 'second_name', 'last_name', 'second_last_name', 'document_type', 'document_number', 'country_id', 'created_by', 'modified_by', 'created_at')->where('status', 1)->skip($request->start)->take($request->start + $request->length)->get();
        $recordsTotal = SatelliteUser::count();
        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('name', function($row){

                $full_name = $row->first_name." ".$row->second_name." ".$row->last_name." ".$row->second_last_name;
                $result = "<div>
                                <span>$full_name</span>
                            </div>";
                return $result;
            })
            ->addColumn('document', function($row){
               $result = "<div>
                                <span>".$row->document->name."</span>
                                <span class='small text-muted'> | $row->document_number</span>
                            </div>";
                return $result;
            })
            ->addColumn('country', function($row){
                $result = "<span style='cursor:pointer' title='".$row->country->name."'>".$row->country->code."</span>";
                return $result;
            })
            ->addColumn('modified', function($row){

                $date = Carbon::parse($row->updated_at, 'UTC');
                $date = $date->isoFormat('D MMMM YYYY');

                $result = "<div>
                                <span>".$row->modified_by_user->first_name." ".$row->modified_by_user->last_name."</span>
                                <span class='small text-muted'> | ".$date." </span>
                            </div>";
                return $result;
            })
            ->addColumn('actions', function($row){
                $edit = (Auth::user()->can('satellite-user-edit'))? "<button  class='btn btn-warning btn-sm' onclick='modifyUser(".$row->id.")'><i class='fa fa-edit'></i></button>"
                    : "";
                $delete = (Auth::user()->can('satellite-user-delete'))? "<button class='btn btn-danger btn-sm' onclick='removeUser(".$row->id.")'><i class='fa fa-trash-alt'></i></button>" : "";
                $result = "
                    $edit
                    $delete
                            ";
                return $result;
            })
            ->addColumn('images', function($row){
                $images = SatelliteUsersImage::where('satellite_user_id', $row->id)->get();
                $result [1] = "<i class='fa fa-times text-danger'></i>";
                $result [2] = "<i class='fa fa-times text-danger ml-2'></i>";
                $result [3] = "<i class='fa fa-times text-danger ml-2'></i>";
                $result [4] = "<i class='fa fa-times text-danger ml-2'></i>";
                foreach ($images as $key => $img) {
                    if ($img['type'] == 1)
                    {
                       $result [1] = "<i class='fa fa-check text-success'></i>";
                    }
                    if ($img['type'] == 2)
                    {
                       $result [2] = "<i class='fa fa-check text-success ml-2'></i>";
                    }
                    if ($img['type'] == 3)
                    {
                       $result [3] = "<i class='fa fa-check text-success ml-2'></i>";
                    }
                    if ($img['type'] == 4)
                    {
                       $result [4] = "<i class='fa fa-check text-success ml-2'></i>";
                    }
                }
                $result = "<div class='col-lg-12'>
                                    <div class='row'>
                                        <div class='col-lg-3'>".$result[1]."</div>
                                        <div class='col-lg-3'>".$result[2]."</div>
                                        <div class='col-lg-3'>".$result[3]."</div>
                                        <div class='col-lg-3'>".$result[4]."</div>
                                    </div>
                                </div>";
                return $result;
            })

            ->rawColumns(['name','document','country','modified','actions','images'])
            ->with([
                'draw' => $request->draw,
                'recordsTotal' => $recordsTotal,
                'recordsFiltered' => $recordsTotal,
            ])
            ->make(true);
    }

    public function getUsers2(Request $request)
    {
        $search = $request->search['value'];

        if ($search != null)
        {
            $query = DB
                ::table('satellite_users AS su')
                ->select(
                    'su.id',
                    'su.first_name',
                    'su.second_name',
                    'su.last_name',
                    'su.second_last_name',
                    'su.document_type',
                    'su.document_number',
                    'su.country_id',
                    'su.created_by',
                    'su.modified_by',
                    'su.created_at',
                    'su.updated_at',
                    'sd.name AS document_name',
                    'gc.name AS country',
                    'us.first_name AS modified_by_name',
                    'us.last_name AS modified_by_last_name'
                )
                ->join('satellite_users_documents_types AS sd', 'su.document_type', 'sd.id')
                ->join('global_countries AS gc', 'su.country_id', 'gc.id')
                ->join('users AS us', 'su.modified_by', 'us.id')
                ->orWhere('su.first_name', 'LIKE', "%$search%")->where('su.status', 1)
                ->orWhere('su.second_name', 'LIKE', "%$search%")->where('su.status', 1)
                ->orWhere('su.last_name', 'LIKE', "%$search%")->where('su.status', 1)
                ->orWhere('su.second_last_name', 'LIKE', "%$search%")->where('su.status', 1)
                ->orWhere('su.document_number', 'LIKE', "%$search%")->where('su.status', 1)
                ->orWhere('su.created_by', 'LIKE', "%$search%")->where('su.status', 1)
                ->orWhere('su.created_at', 'LIKE', "%$search%")->where('su.status', 1)
                ->orWhere(DB::raw("CONCAT_WS(' ', su.first_name, su.second_name, su.last_name, su.second_last_name)"), 'LIKE', "%$search%")->where('su.status', 1)
                ->orWhere(DB::raw("CONCAT_WS(' ', su.first_name, su.last_name)"), 'LIKE', "%$search%")->where('su.status', 1);

            $recordsTotal = $query->get()->count();
            $query = $query->skip($request->start)->take($request->start + $request->length)->get();
        }
        else
        {
            $query = DB
                ::table('satellite_users AS su')
                ->select(
                    'su.id',
                    'su.first_name',
                    'su.second_name',
                    'su.last_name',
                    'su.second_last_name',
                    'su.document_type',
                    'su.document_number',
                    'su.country_id',
                    'su.created_by',
                    'su.modified_by',
                    'su.created_at',
                    'su.updated_at',
                    'sd.name AS document_name',
                    'gc.name AS country',
                    'us.first_name AS modified_by_name',
                    'us.last_name AS modified_by_last_name'
                )
                ->join('satellite_users_documents_types AS sd', 'su.document_type', 'sd.id')
                ->join('global_countries AS gc', 'su.country_id', 'gc.id')
                ->leftjoin('users AS us', 'su.modified_by', 'us.id')
                ->where('su.status', 1)
                ->orderBy('su.id');

            $recordsTotal = $query->get()->count();
            $query = $query->skip($request->start)->take($request->start + $request->length)->get();


        }

        $data = [];

        foreach ($query as $key => $row)
        {
            $full_name = "<div><span>{$row->first_name} {$row->second_name} {$row->last_name} {$row->second_last_name}</span></div>";
            $document  = "<div>
                            <span>".$row->document_name."</span>
                            <span class='small text-muted'> | $row->document_number</span>
                        </div>";
            $country   = "<span style='cursor:pointer' title='".$row->country."'>".$row->country."</span>";
            $modified  = "<div>
                            <span>" .$row->modified_by_name . " " . $row->modified_by_last_name . "</span>
                            <span class='small text-muted'> | " . Carbon::parse($row->updated_at, 'UTC')->isoFormat('D MMMM YYYY') . " </span>
                        </div>";

            $edit   = (Auth::user()->can('satellite-user-edit'))? "<button  class='btn btn-warning btn-sm' onclick='modifyUser(".$row->id.")'><i class='fa fa-edit'></i></button>" : "";
            $delete = (Auth::user()->can('satellite-user-delete'))? "<button class='btn btn-danger btn-sm' onclick='removeUser(".$row->id.")'><i class='fa fa-trash-alt'></i></button>" : "";
            $actions = "$edit $delete";

            $user_images = SatelliteUsersImage::where('satellite_user_id', $row->id)->get();
            $result[1] = "<i class='fa fa-times text-danger'></i>";
            $result[2] = "<i class='fa fa-times text-danger ml-2'></i>";
            $result[3] = "<i class='fa fa-times text-danger ml-2'></i>";
            $result[4] = "<i class='fa fa-times text-danger ml-2'></i>";

            foreach ($user_images as $key_img => $img) {
                if ($img['type'] == 1)
                {
                    $result[1] = "<i class='fa fa-check text-success'></i>";
                }
                if ($img['type'] == 2)
                {
                    $result[2] = "<i class='fa fa-check text-success ml-2'></i>";
                }
                if ($img['type'] == 3)
                {
                    $result[3] = "<i class='fa fa-check text-success ml-2'></i>";
                }
                if ($img['type'] == 4)
                {
                    $result[4] = "<i class='fa fa-check text-success ml-2'></i>";
                }
            }

            $images = "<div class='col-lg-12'>
                                    <div class='row'>
                                        <div class='col-lg-3'>".$result[1]."</div>
                                        <div class='col-lg-3'>".$result[2]."</div>
                                        <div class='col-lg-3'>".$result[3]."</div>
                                        <div class='col-lg-3'>".$result[4]."</div>
                                    </div>
                                </div>";
            $data[] = [
                'name' => $full_name,
                'document' => $document,
                'country' => $country,
                'modified' => $modified,
                'actions' => $actions,
                'images' => $images,
            ];
        }

        return DataTables::of($query)
            ->with([
                'draw' => $request->draw,
                'recordsTotal' => $recordsTotal,
                'recordsFiltered' => $recordsTotal,
                'data' => $data,
            ])
            ->make(true);
    }

    public function getAccountsData($status, $page_select)
    {
        if ($status > 0 && $page_select > 0)
            $data = SatelliteAccount::select('satellite_accounts.*', 'satellite_owners.owner', 'users.first_name as user_first_name', 'users.last_name as user_last_name')
                ->join('satellite_owners', 'satellite_owners.id', 'satellite_accounts.owner_id')
                ->join('users', 'users.id', 'satellite_accounts.modified_by')->where('page_id', $page_select)->where( 'status_id', $status)->where( 'is_user',
                    0)->get();
        elseif($status > 0)
            $data = SatelliteAccount::select('satellite_accounts.*', 'satellite_owners.owner', 'users.first_name as user_first_name', 'users.last_name as user_last_name')
                ->join('satellite_owners', 'satellite_owners.id', 'satellite_accounts.owner_id')
                ->join('users', 'users.id', 'satellite_accounts.modified_by')->where( 'status_id', $status)->where( 'is_user',
                    0)->get();
        elseif($page_select > 0)
            $data = SatelliteAccount::select('satellite_accounts.*', 'satellite_owners.owner', 'users.first_name as user_first_name', 'users.last_name as user_last_name')
                ->join('satellite_owners', 'satellite_owners.id', 'satellite_accounts.owner_id')
                ->join('users', 'users.id', 'satellite_accounts.modified_by')->where('page_id', $page_select)->where( 'is_user',
                    0)->get();
        else
            $data = SatelliteAccount::select('satellite_accounts.*', 'satellite_owners.owner', 'users.first_name as user_first_name', 'users.last_name as user_last_name')
                ->join('satellite_owners', 'satellite_owners.id', 'satellite_accounts.owner_id')
                ->join('users', 'users.id', 'satellite_accounts.modified_by')->where( 'is_user',
                    0)->get();

        return $data;
    }

    public function listAccounts($owner_id = null)
    {
        $documents = SatelliteUsersDocumentsType::select('id', 'name')->get();
        $status = SatelliteAccountStatus::select('id', 'name')->get();
        $pages = DB::table('satellite_templates_pages_fields')->where('template_type_id', 1)->get();
        $countries = DB::table('global_countries')->select('id', 'name', 'code')->get();
        $owner_name = "";
        if ($owner_id != null)
        {
            $owner = SatelliteOwner::select('owner')->where('id', $owner_id)->first();
            $owner_name = $owner->owner;
        }
        return view("adminModules.satellite.account.list")->with([
            "documents" => $documents,
            "countries" => $countries,
            "pages" => $pages,
            "status" => $status,
            "owner_id" => $owner_id,
            "owner_name" => $owner_name,
        ]);
    }

    /*public function getAccounts(Request $request)
    {
        $data = $this->getAccountsData($request->status, $request->page_select);
        return DataTables::of($data)->toJson();
    }*/

    /*public function getAccounts(Request $request)
    {
        $data = "";
        $search = $request->search['value'];
        if ($search != null)
        {
            $query = DB::table('satellite_accounts as sa')
                ->select('so.id', 'so.owner', 'sa.nick', 'sa.status_id' ,'sa.page_id', 'sp.name as page','sa.first_name','sa.second_name',
                    'sa.last_name','sa.second_last_name', 'sa.access', 'sa.password', 'sa.id AS account_id',
                    'u.first_name as user_first_name', 'u.last_name as user_last_name',
                    'status.id' ,'status.name as status_name', 'status.color as status_color', 'status.background as status_background', 'sa.updated_at')
                ->join('satellite_owners as so', 'so.id', 'sa.owner_id' )
                ->join('setting_pages as sp', 'sp.id', 'sa.page_id')
                ->join('users as u', 'u.id', 'sa.modified_by')
                ->join('satellite_accounts_status as status', 'status.id', 'sa.status_id');


            if ($request->status != 0)
            {

                    $query = $query->where('sa.nick', 'LIKE', "%$search%")->where('sa.status_id', $request->status)
                        ->orWhere('so.owner', 'LIKE', "%$search%")->where('sa.status_id', $request->status)
                        ->orWhere('sa.first_name', 'LIKE', "%$search%")->where('sa.status_id', $request->status)
                        ->orWhere('sa.second_name', 'LIKE', "%$search%")->where('sa.status_id', $request->status)
                        ->orWhere(DB::raw("CONCAT_WS(' ', sa.first_name, sa.second_name, sa.last_name, sa.second_last_name)"), 'LIKE', "%$search%")
                        ->where('sa.status_id', $request->status)
                        ->orWhere(DB::raw("CONCAT_WS(' ', sa.first_name, sa.last_name, sa.second_last_name)"), 'LIKE', "%$search%")
                        ->where('sa.status_id', $request->status)
                        ->orWhere('sa.first_name', 'LIKE', "%$search%")->where('sa.status_id', $request->status)
                        ->orWhere('sa.second_name', 'LIKE', "%$search%")->where('sa.status_id', $request->status)
                        ->orWhere('sa.last_name', 'LIKE', "%$search%")->where('sa.status_id', $request->status)
                        ->orWhere('sa.second_last_name', 'LIKE', "%$search%")->where('sa.status_id', $request->status)
                        ->orWhere('sa.access', 'LIKE', "%$search%")->where('sa.status_id', $request->status)
                        ->orWhere('sa.password', 'LIKE', "%$search%")->where('sa.status_id', $request->status)
                        ->orWhere(DB::raw("CONCAT_WS(' ', u.first_name, u.last_name)"), 'LIKE', "%$search%")->where('sa.status_id', $request->status)
                        ->orWhere('u.first_name', 'LIKE', "%$search%")->where('sa.status_id', $request->status)
                        ->orWhere('u.last_name', 'LIKE', "%$search%")->where('sa.status_id', $request->status)
                        ->orWhere('status.name', 'LIKE', "%$search%")->where('sa.status_id', $request->status);


            }
            if ($request->page_select != 0)
            {
                $query = $query->where('sa.nick', 'LIKE', "%$search%")->where('sa.page_id', $request->page_select)
                    ->orWhere('so.owner', 'LIKE', "%$search%")->where('sa.page_id', $request->page_select)
                    ->orWhere('sa.first_name', 'LIKE', "%$search%")->where('sa.page_id', $request->page_select)
                    ->orWhere('sa.second_name', 'LIKE', "%$search%")->where('sa.page_id', $request->page_select)
                    ->orWhere(DB::raw("CONCAT_WS(' ', sa.first_name, sa.second_name, sa.last_name, sa.second_last_name)"), 'LIKE', "%$search%")
                    ->where('sa.page_id', $request->page_select)
                    ->orWhere(DB::raw("CONCAT_WS(' ', sa.first_name, sa.last_name, sa.second_last_name)"), 'LIKE', "%$search%")
                    ->where('sa.page_id', $request->page_select)
                    ->orWhere('sa.first_name', 'LIKE', "%$search%")->where('sa.page_id', $request->page_select)
                    ->orWhere('sa.second_name', 'LIKE', "%$search%")->where('sa.page_id', $request->page_select)
                    ->orWhere('sa.last_name', 'LIKE', "%$search%")->where('sa.page_id', $request->page_select)
                    ->orWhere('sa.second_last_name', 'LIKE', "%$search%")->where('sa.page_id', $request->page_select)
                    ->orWhere('sa.access', 'LIKE', "%$search%")->where('sa.page_id', $request->page_select)
                    ->orWhere('sa.password', 'LIKE', "%$search%")->where('sa.page_id', $request->page_select)
                    ->orWhere(DB::raw("CONCAT_WS(' ', u.first_name, u.last_name)"), 'LIKE', "%$search%")->where('sa.page_id', $request->page_select)
                    ->orWhere('u.first_name', 'LIKE', "%$search%")->where('sa.page_id', $request->page_select)
                    ->orWhere('u.last_name', 'LIKE', "%$search%")->where('sa.page_id', $request->page_select)
                    ->orWhere('status.name', 'LIKE', "%$search%")->where('sa.page_id', $request->page_select);
            }
            if ($request->page_select != 0 && $request->status != 0)
            {
                $query = $query->where('sa.nick', 'LIKE', "%$search%")->where('sa.page_id', $request->page_select)->where('sa.status_id', $request->status)
                    ->orWhere('so.owner', 'LIKE', "%$search%")->where('sa.page_id', $request->page_select)->where('sa.status_id', $request->status)
                    ->orWhere('sa.first_name', 'LIKE', "%$search%")->where('sa.page_id', $request->page_select)->where('sa.status_id', $request->status)
                    ->orWhere('sa.second_name', 'LIKE', "%$search%")->where('sa.page_id', $request->page_select)->where('sa.status_id', $request->status)
                    ->orWhere(DB::raw("CONCAT_WS(' ', sa.first_name, sa.second_name, sa.last_name, sa.second_last_name)"), 'LIKE', "%$search%")
                    ->where('sa.page_id', $request->page_select)->where('sa.status_id', $request->status)
                    ->orWhere(DB::raw("CONCAT_WS(' ', sa.first_name, sa.last_name, sa.second_last_name)"), 'LIKE', "%$search%")
                    ->where('sa.page_id', $request->page_select)->where('sa.status_id', $request->status)
                    ->orWhere('sa.first_name', 'LIKE', "%$search%")->where('sa.page_id', $request->page_select)->where('sa.status_id', $request->status)
                    ->orWhere('sa.second_name', 'LIKE', "%$search%")->where('sa.page_id', $request->page_select)->where('sa.status_id', $request->status)
                    ->orWhere('sa.last_name', 'LIKE', "%$search%")->where('sa.page_id', $request->page_select)->where('sa.status_id', $request->status)
                    ->orWhere('sa.second_last_name', 'LIKE', "%$search%")->where('sa.page_id', $request->page_select)->where('sa.status_id', $request->status)
                    ->orWhere('sa.access', 'LIKE', "%$search%")->where('sa.page_id', $request->page_select)->where('sa.status_id', $request->status)
                    ->orWhere('sa.password', 'LIKE', "%$search%")->where('sa.page_id', $request->page_select)->where('sa.status_id', $request->status)
                    ->orWhere(DB::raw("CONCAT_WS(' ', u.first_name, u.last_name)"), 'LIKE', "%$search%")
                    ->where('sa.page_id', $request->page_select)->where('sa.status_id', $request->status)
                    ->orWhere('u.first_name', 'LIKE', "%$search%")->where('sa.page_id', $request->page_select)->where('sa.status_id', $request->status)
                    ->orWhere('u.last_name', 'LIKE', "%$search%")->where('sa.page_id', $request->page_select)->where('sa.status_id', $request->status)
                    ->orWhere('status.name', 'LIKE', "%$search%")->where('sa.page_id', $request->page_select)->where('sa.status_id', $request->status);
            }
            if ($request->page_select == 0 && $request->status == 0)
            {
                $query = $query->where('sa.nick', 'LIKE', "%$search%")
                    ->orWhere('so.owner', 'LIKE', "%$search%")
                    ->orWhere('sa.first_name', 'LIKE', "%$search%")->orWhere('sa.second_name', 'LIKE', "%$search%")
                    ->orWhere(DB::raw("CONCAT_WS(' ', sa.first_name, sa.second_name, sa.last_name, sa.second_last_name)"), 'LIKE', "%$search%")
                    ->orWhere(DB::raw("CONCAT_WS(' ', sa.first_name, sa.last_name, sa.second_last_name)"), 'LIKE', "%$search%")
                    ->orWhere('sa.first_name', 'LIKE', "%$search%")->orWhere('sa.second_name', 'LIKE', "%$search%")
                    ->orWhere('sa.last_name', 'LIKE', "%$search%")->orWhere('sa.second_last_name', 'LIKE', "%$search%")
                    ->orWhere('sa.access', 'LIKE', "%$search%")
                    ->orWhere('sa.password', 'LIKE', "%$search%")
                    ->orWhere(DB::raw("CONCAT_WS(' ', u.first_name, u.last_name)"), 'LIKE', "%$search%")
                    ->orWhere('u.first_name', 'LIKE', "%$search%")
                    ->orWhere('u.last_name', 'LIKE', "%$search%")
                    ->orWhere('status.name', 'LIKE', "%$search%");
            }

            $recordsTotal = $query->get()->count();
            $query = $query->skip($request->start)->take($request->start + $request->length)->get();
        }
        else
        {
            $query = DB::table('satellite_accounts as sa')
                ->select('so.owner', 'sa.nick', 'sa.status_id' ,'sa.page_id', 'sp.name as page','sa.first_name','sa.second_name',
                    'sa.last_name','sa.second_last_name', 'sa.access', 'sa.password', 'sa.id AS account_id',
                    'u.first_name as user_first_name', 'u.last_name as user_last_name',
                    'status.id' ,'status.name as status_name', 'status.color as status_color', 'status.background as status_background', 'sa.updated_at')
                ->join('satellite_owners as so', 'so.id', 'sa.owner_id' )
                ->join('setting_pages as sp', 'sp.id', 'sa.page_id')
                ->join('users as u', 'u.id', 'sa.modified_by')
                ->join('satellite_accounts_status as status', 'status.id', 'sa.status_id');

            if ($request->status != 0)
            {
                $query = $query->where('sa.status_id', $request->status);
            }
            if ($request->page_select != 0)
            {
                $query = $query->where('sa.page_id', $request->page_select);
            }
            if ($request->page_select != 0 && $request->status != 0)
            {
                $query = $query->where('sa.page_id', $request->page_select)->where('sa.status_id', $request->status);
            }

            $recordsTotal = $query->get()->count();
            $query = $query->skip($request->start)->take($request->start + $request->length)->get();
        }



        $result[0]['owner'] = "";
        $result[0]['nick'] = "";
        $result[0]['name'] = "";
        $result[0]['access'] = "";
        $result[0]['partner'] = "";
        $result[0]['modified'] = "";
        $result[0]['status'] = "";
        $result[0]['actions'] = "";

        foreach ($query as $key => $row)
        {
            $result[$key]['owner'] = "<div><span>".$row->owner."</span></div>";
            $result[$key]['nick'] = "<div><span>".$row->nick."</span><br>
                                    <span class='small text-muted'> ".$row->page." </span></div>";
            $full_name = $row->first_name." ".$row->second_name." ".$row->last_name." ".$row->second_last_name;
            $result[$key]['name'] = "<div><span>$full_name</span></div>";
            $result[$key]['access'] = "<div><span>Email: ".chunk_split($row->access,40,"<br>")."</span>
                                <span class='small text-muted'> Clave: ".$row->password." </span>
                            </div>";
            $partner_field = "";
            $partners = SatelliteAccountPartner::select('id', 'name')->where('account_id', $row->id)->get();
            foreach ($partners as $partner) {

                $partner_field .= ($partner_field == "")? $partner->name : "<br> ".$partner->name;
            }
            if ($partner_field == "")
            {
                $result[$key]['partner'] = "<div style='cursor:pointer'>
                                <span class='badge badge-pill badge-secondary'>0</span>
                            </div>";
            }
            else
            {
                $result[$key]['partner'] = "<div style='cursor:pointer' data-toggle='tooltip' data-html='true' data-original-title='".$partner_field."'>
                                <span class='badge badge-pill badge-info'>".count($partners)."</span>
                            </div>";
            }
            $date = Carbon::parse($row->updated_at, 'UTC');
            $date = $date->isoFormat('D MMMM YYYY');
            $result[$key]['modified'] = "<div>
                                <span>".$row->user_first_name." ".$row->user_last_name."</span><br>
                                <span class='small text-muted'> ".$date." </span>
                            </div>";
            $result[$key]['status'] = "<div>
                                <span class='badge badge-pill' style='background:".$row->status_background."; color:".$row->status_color." '>".$row->status_name."</span>
                            </div>";
            if (Auth::user()->can('satellite-account-edit'))
            {
                $result[$key]['actions'] = "<button  class='btn btn-warning btn-sm' onclick='modifyAccount(".$row->account_id.")'><i class='fa fa-edit'></i></button>
                            <button class='btn btn-info btn-sm' data-target='#modal-payment-account'
                            data-toggle='modal' onclick='statisticSummary(".$row->account_id.")'><i class='fa fa-chart-line'></i></button>";
            }
            else
            {

                $result[$key]['actions'] = "<button class='btn btn-info btn-sm' data-target='#modal-payment-account'
                            data-toggle='modal' onclick='statisticSummary(".$row->id.")'><i class='fa fa-chart-line'></i></button>";
            }

        }
        return DataTables::of($query)
            ->with([
                'draw' => $request->draw,
                'recordsTotal' => $recordsTotal,
                'recordsFiltered' => $recordsTotal,
                'data' => $result,
            ])
            ->make(true);
    }*/

    public function getAccounts(Request $request)
    {
        $owner_id = $request->owner_id;
        $data = "";
        $search = $request->search['value'];
        if ($search != null)
        {
            $query = DB::table('satellite_accounts as sa')
                ->select('so.id', 'so.owner', 'sa.nick', 'sa.status_id' ,'sa.page_id', 'sp.name as page','sa.first_name','sa.second_name',
                    'sa.last_name','sa.second_last_name', 'sa.access', 'sa.password', 'sa.id AS account_id',
                    'u.first_name as user_first_name', 'u.last_name as user_last_name',
                    'status.id' ,'status.name as status_name', 'status.color as status_color', 'status.background as status_background', 'sa.updated_at')
                ->join('satellite_owners as so', 'so.id', 'sa.owner_id' )
                ->join('setting_pages as sp', 'sp.id', 'sa.page_id')
                ->leftJoin('users as u', 'u.id', 'sa.modified_by')
                ->join('satellite_accounts_status as status', 'status.id', 'sa.status_id');

            if ($request->status != 0)
            {
                if ($owner_id != null)
                {
                    $query = $query->where('sa.nick', 'LIKE', "%$search%")->where('sa.status_id', $request->status)->where('so.id', $owner_id)
                        ->orWhere('so.owner', 'LIKE', "%$search%")->where('sa.status_id', $request->status)->where('so.id', $owner_id)
                        ->orWhere('sa.first_name', 'LIKE', "%$search%")->where('sa.status_id', $request->status)->where('so.id', $owner_id)
                        ->orWhere('sa.second_name', 'LIKE', "%$search%")->where('sa.status_id', $request->status)->where('so.id', $owner_id)
                        ->orWhere(DB::raw("CONCAT_WS(' ', sa.first_name, sa.second_name, sa.last_name, sa.second_last_name)"), 'LIKE', "%$search%")
                        ->where('sa.status_id', $request->status)->where('so.id', $owner_id)
                        ->orWhere(DB::raw("CONCAT_WS(' ', sa.first_name, sa.last_name, sa.second_last_name)"), 'LIKE', "%$search%")
                        ->where('sa.status_id', $request->status)->where('so.id', $owner_id)
                        ->orWhere('sa.first_name', 'LIKE', "%$search%")->where('sa.status_id', $request->status)->where('so.id', $owner_id)
                        ->orWhere('sa.second_name', 'LIKE', "%$search%")->where('sa.status_id', $request->status)->where('so.id', $owner_id)
                        ->orWhere('sa.last_name', 'LIKE', "%$search%")->where('sa.status_id', $request->status)->where('so.id', $owner_id)
                        ->orWhere('sa.second_last_name', 'LIKE', "%$search%")->where('sa.status_id', $request->status)->where('so.id', $owner_id)
                        ->orWhere('sa.access', 'LIKE', "%$search%")->where('sa.status_id', $request->status)->where('so.id', $owner_id)
                        ->orWhere('sa.password', 'LIKE', "%$search%")->where('sa.status_id', $request->status)->where('so.id', $owner_id)
                        ->orWhere(DB::raw("CONCAT_WS(' ', u.first_name, u.last_name)"), 'LIKE', "%$search%")->where('sa.status_id', $request->status)->where('so.id', $owner_id)
                        ->orWhere('u.first_name', 'LIKE', "%$search%")->where('sa.status_id', $request->status)->where('so.id', $owner_id)
                        ->orWhere('u.last_name', 'LIKE', "%$search%")->where('sa.status_id', $request->status)->where('so.id', $owner_id)
                        ->orWhere('status.name', 'LIKE', "%$search%")->where('sa.status_id', $request->status)->where('so.id', $owner_id);
                }
                else{
                    $query = $query->where('sa.nick', 'LIKE', "%$search%")->where('sa.status_id', $request->status)
                        ->orWhere('so.owner', 'LIKE', "%$search%")->where('sa.status_id', $request->status)
                        ->orWhere('sa.first_name', 'LIKE', "%$search%")->where('sa.status_id', $request->status)
                        ->orWhere('sa.second_name', 'LIKE', "%$search%")->where('sa.status_id', $request->status)
                        ->orWhere(DB::raw("CONCAT_WS(' ', sa.first_name, sa.second_name, sa.last_name, sa.second_last_name)"), 'LIKE', "%$search%")
                        ->where('sa.status_id', $request->status)
                        ->orWhere(DB::raw("CONCAT_WS(' ', sa.first_name, sa.last_name, sa.second_last_name)"), 'LIKE', "%$search%")
                        ->where('sa.status_id', $request->status)
                        ->orWhere('sa.first_name', 'LIKE', "%$search%")->where('sa.status_id', $request->status)
                        ->orWhere('sa.second_name', 'LIKE', "%$search%")->where('sa.status_id', $request->status)
                        ->orWhere('sa.last_name', 'LIKE', "%$search%")->where('sa.status_id', $request->status)
                        ->orWhere('sa.second_last_name', 'LIKE', "%$search%")->where('sa.status_id', $request->status)
                        ->orWhere('sa.access', 'LIKE', "%$search%")->where('sa.status_id', $request->status)
                        ->orWhere('sa.password', 'LIKE', "%$search%")->where('sa.status_id', $request->status)
                        ->orWhere(DB::raw("CONCAT_WS(' ', u.first_name, u.last_name)"), 'LIKE', "%$search%")->where('sa.status_id', $request->status)
                        ->orWhere('u.first_name', 'LIKE', "%$search%")->where('sa.status_id', $request->status)
                        ->orWhere('u.last_name', 'LIKE', "%$search%")->where('sa.status_id', $request->status)
                        ->orWhere('status.name', 'LIKE', "%$search%")->where('sa.status_id', $request->status);
                }

            }
            if ($request->page_select != 0)
            {
                if ($owner_id != null)
                {
                    $query = $query->orWhere('sa.nick', 'LIKE', "%$search%")->where('sa.page_id', $request->page_select)->where('so.id', $owner_id)
                        ->orWhere('so.owner', 'LIKE', "%$search%")->where('sa.page_id', $request->page_select)->where('so.id', $owner_id)
                        ->orWhere('sa.first_name', 'LIKE', "%$search%")->where('sa.page_id', $request->page_select)->where('so.id', $owner_id)
                        ->orWhere('sa.second_name', 'LIKE', "%$search%")->where('sa.page_id', $request->page_select)->where('so.id', $owner_id)
                        ->orWhere(DB::raw("CONCAT_WS(' ', sa.first_name, sa.second_name, sa.last_name, sa.second_last_name)"), 'LIKE', "%$search%")
                        ->where('sa.page_id', $request->page_select)->where('so.id', $owner_id)
                        ->orWhere(DB::raw("CONCAT_WS(' ', sa.first_name, sa.last_name, sa.second_last_name)"), 'LIKE', "%$search%")
                        ->where('sa.page_id', $request->page_select)->where('so.id', $owner_id)
                        ->orWhere('sa.first_name', 'LIKE', "%$search%")->where('sa.page_id', $request->page_select)->where('so.id', $owner_id)
                        ->orWhere('sa.second_name', 'LIKE', "%$search%")->where('sa.page_id', $request->page_select)->where('so.id', $owner_id)
                        ->orWhere('sa.last_name', 'LIKE', "%$search%")->where('sa.page_id', $request->page_select)->where('so.id', $owner_id)
                        ->orWhere('sa.second_last_name', 'LIKE', "%$search%")->where('sa.page_id', $request->page_select)->where('so.id', $owner_id)
                        ->orWhere('sa.access', 'LIKE', "%$search%")->where('sa.page_id', $request->page_select)->where('so.id', $owner_id)
                        ->orWhere('sa.password', 'LIKE', "%$search%")->where('sa.page_id', $request->page_select)->where('so.id', $owner_id)
                        ->orWhere(DB::raw("CONCAT_WS(' ', u.first_name, u.last_name)"), 'LIKE', "%$search%")->where('sa.page_id', $request->page_select)
                        ->where('so.id', $owner_id)
                        ->orWhere('u.first_name', 'LIKE', "%$search%")->where('sa.page_id', $request->page_select)->where('so.id', $owner_id)
                        ->orWhere('u.last_name', 'LIKE', "%$search%")->where('sa.page_id', $request->page_select)->where('so.id', $owner_id)
                        ->orWhere('status.name', 'LIKE', "%$search%")->where('sa.page_id', $request->page_select)->where('so.id', $owner_id);
                }
                else{
                    $query = $query->where('sa.nick', 'LIKE', "%$search%")->where('sa.page_id', $request->page_select)
                        ->orWhere('so.owner', 'LIKE', "%$search%")->where('sa.page_id', $request->page_select)
                        ->orWhere('sa.first_name', 'LIKE', "%$search%")->where('sa.page_id', $request->page_select)
                        ->orWhere('sa.second_name', 'LIKE', "%$search%")->where('sa.page_id', $request->page_select)
                        ->orWhere(DB::raw("CONCAT_WS(' ', sa.first_name, sa.second_name, sa.last_name, sa.second_last_name)"), 'LIKE', "%$search%")
                        ->where('sa.page_id', $request->page_select)
                        ->orWhere(DB::raw("CONCAT_WS(' ', sa.first_name, sa.last_name, sa.second_last_name)"), 'LIKE', "%$search%")
                        ->where('sa.page_id', $request->page_select)
                        ->orWhere('sa.first_name', 'LIKE', "%$search%")->where('sa.page_id', $request->page_select)
                        ->orWhere('sa.second_name', 'LIKE', "%$search%")->where('sa.page_id', $request->page_select)
                        ->orWhere('sa.last_name', 'LIKE', "%$search%")->where('sa.page_id', $request->page_select)
                        ->orWhere('sa.second_last_name', 'LIKE', "%$search%")->where('sa.page_id', $request->page_select)
                        ->orWhere('sa.access', 'LIKE', "%$search%")->where('sa.page_id', $request->page_select)
                        ->orWhere('sa.password', 'LIKE', "%$search%")->where('sa.page_id', $request->page_select)
                        ->orWhere(DB::raw("CONCAT_WS(' ', u.first_name, u.last_name)"), 'LIKE', "%$search%")->where('sa.page_id', $request->page_select)
                        ->orWhere('u.first_name', 'LIKE', "%$search%")->where('sa.page_id', $request->page_select)
                        ->orWhere('u.last_name', 'LIKE', "%$search%")->where('sa.page_id', $request->page_select)
                        ->orWhere('status.name', 'LIKE', "%$search%")->where('sa.page_id', $request->page_select);
                }

            }
            if ($request->page_select != 0 && $request->status != 0)
            {
                if ($owner_id != null)
                {
                    $query = $query->where('sa.nick', 'LIKE', "%$search%")->where('sa.page_id', $request->page_select)->where('sa.status_id', $request->status)
                        ->where('so.id', $owner_id)
                        ->orWhere('so.owner', 'LIKE', "%$search%")->where('sa.page_id', $request->page_select)->where('sa.status_id', $request->status)
                        ->where('so.id', $owner_id)
                        ->orWhere('sa.first_name', 'LIKE', "%$search%")->where('sa.page_id', $request->page_select)->where('sa.status_id', $request->status)
                        ->where('so.id', $owner_id)
                        ->orWhere('sa.second_name', 'LIKE', "%$search%")->where('sa.page_id', $request->page_select)->where('sa.status_id', $request->status)
                        ->where('so.id', $owner_id)
                        ->orWhere(DB::raw("CONCAT_WS(' ', sa.first_name, sa.second_name, sa.last_name, sa.second_last_name)"), 'LIKE', "%$search%")
                        ->where('sa.page_id', $request->page_select)->where('sa.status_id', $request->status)->where('so.id', $owner_id)
                        ->orWhere(DB::raw("CONCAT_WS(' ', sa.first_name, sa.last_name, sa.second_last_name)"), 'LIKE', "%$search%")
                        ->where('sa.page_id', $request->page_select)->where('sa.status_id', $request->status)->where('so.id', $owner_id)
                        ->orWhere('sa.first_name', 'LIKE', "%$search%")->where('sa.page_id', $request->page_select)->where('sa.status_id', $request->status)
                        ->where('so.id', $owner_id)
                        ->orWhere('sa.second_name', 'LIKE', "%$search%")->where('sa.page_id', $request->page_select)->where('sa.status_id', $request->status)
                        ->where('so.id', $owner_id)
                        ->orWhere('sa.last_name', 'LIKE', "%$search%")->where('sa.page_id', $request->page_select)->where('sa.status_id', $request->status)
                        ->where('so.id', $owner_id)
                        ->orWhere('sa.second_last_name', 'LIKE', "%$search%")->where('sa.page_id', $request->page_select)->where('sa.status_id', $request->status)
                        ->where('so.id', $owner_id)
                        ->orWhere('sa.access', 'LIKE', "%$search%")->where('sa.page_id', $request->page_select)->where('sa.status_id', $request->status)
                        ->where('so.id', $owner_id)
                        ->orWhere('sa.password', 'LIKE', "%$search%")->where('sa.page_id', $request->page_select)->where('sa.status_id', $request->status)
                        ->where('so.id', $owner_id)
                        ->orWhere(DB::raw("CONCAT_WS(' ', u.first_name, u.last_name)"), 'LIKE', "%$search%")
                        ->where('sa.page_id', $request->page_select)->where('sa.status_id', $request->status)->where('so.id', $owner_id)
                        ->orWhere('u.first_name', 'LIKE', "%$search%")->where('sa.page_id', $request->page_select)->where('sa.status_id', $request->status)
                        ->where('so.id', $owner_id)
                        ->orWhere('u.last_name', 'LIKE', "%$search%")->where('sa.page_id', $request->page_select)->where('sa.status_id', $request->status)
                        ->where('so.id', $owner_id)
                        ->orWhere('status.name', 'LIKE', "%$search%")->where('sa.page_id', $request->page_select)->where('sa.status_id', $request->status)
                        ->where('so.id', $owner_id);
                }
                else{
                    $query = $query->where('sa.nick', 'LIKE', "%$search%")->where('sa.page_id', $request->page_select)->where('sa.status_id', $request->status)
                        ->orWhere('so.owner', 'LIKE', "%$search%")->where('sa.page_id', $request->page_select)->where('sa.status_id', $request->status)
                        ->orWhere('sa.first_name', 'LIKE', "%$search%")->where('sa.page_id', $request->page_select)->where('sa.status_id', $request->status)
                        ->orWhere('sa.second_name', 'LIKE', "%$search%")->where('sa.page_id', $request->page_select)->where('sa.status_id', $request->status)
                        ->orWhere(DB::raw("CONCAT_WS(' ', sa.first_name, sa.second_name, sa.last_name, sa.second_last_name)"), 'LIKE', "%$search%")
                        ->where('sa.page_id', $request->page_select)->where('sa.status_id', $request->status)
                        ->orWhere(DB::raw("CONCAT_WS(' ', sa.first_name, sa.last_name, sa.second_last_name)"), 'LIKE', "%$search%")
                        ->where('sa.page_id', $request->page_select)->where('sa.status_id', $request->status)
                        ->orWhere('sa.first_name', 'LIKE', "%$search%")->where('sa.page_id', $request->page_select)->where('sa.status_id', $request->status)
                        ->orWhere('sa.second_name', 'LIKE', "%$search%")->where('sa.page_id', $request->page_select)->where('sa.status_id', $request->status)
                        ->orWhere('sa.last_name', 'LIKE', "%$search%")->where('sa.page_id', $request->page_select)->where('sa.status_id', $request->status)
                        ->orWhere('sa.second_last_name', 'LIKE', "%$search%")->where('sa.page_id', $request->page_select)->where('sa.status_id', $request->status)
                        ->orWhere('sa.access', 'LIKE', "%$search%")->where('sa.page_id', $request->page_select)->where('sa.status_id', $request->status)
                        ->orWhere('sa.password', 'LIKE', "%$search%")->where('sa.page_id', $request->page_select)->where('sa.status_id', $request->status)
                        ->orWhere(DB::raw("CONCAT_WS(' ', u.first_name, u.last_name)"), 'LIKE', "%$search%")
                        ->where('sa.page_id', $request->page_select)->where('sa.status_id', $request->status)
                        ->orWhere('u.first_name', 'LIKE', "%$search%")->where('sa.page_id', $request->page_select)->where('sa.status_id', $request->status)
                        ->orWhere('u.last_name', 'LIKE', "%$search%")->where('sa.page_id', $request->page_select)->where('sa.status_id', $request->status)
                        ->orWhere('status.name', 'LIKE', "%$search%")->where('sa.page_id', $request->page_select)->where('sa.status_id', $request->status);
                }

            }
            if ($request->page_select == 0 && $request->status == 0)
            {
                if ($owner_id != null)
                {
                    $query = $query->where('sa.nick', 'LIKE', "%$search%")->where('so.id', $owner_id)
                        ->orWhere('so.owner', 'LIKE', "%$search%")->where('so.id', $owner_id)
                        ->orWhere('sa.first_name', 'LIKE', "%$search%")->where('so.id', $owner_id)
                        ->orWhere('sa.second_name', 'LIKE', "%$search%")->where('so.id', $owner_id)
                        ->orWhere(DB::raw("CONCAT_WS(' ', sa.first_name, sa.second_name, sa.last_name, sa.second_last_name)"), 'LIKE', "%$search%")->where('so.id', $owner_id)
                        ->orWhere(DB::raw("CONCAT_WS(' ', sa.first_name, sa.last_name, sa.second_last_name)"), 'LIKE', "%$search%")->where('so.id', $owner_id)
                        ->orWhere('sa.first_name', 'LIKE', "%$search%")->where('so.id', $owner_id)
                        ->orWhere('sa.second_name', 'LIKE', "%$search%")->where('so.id', $owner_id)
                        ->orWhere('sa.last_name', 'LIKE', "%$search%")->where('so.id', $owner_id)
                        ->orWhere('sa.second_last_name', 'LIKE', "%$search%")->where('so.id', $owner_id)
                        ->orWhere('sa.access', 'LIKE', "%$search%")->where('so.id', $owner_id)
                        ->orWhere('sa.password', 'LIKE', "%$search%")->where('so.id', $owner_id)
                        ->orWhere(DB::raw("CONCAT_WS(' ', u.first_name, u.last_name)"), 'LIKE', "%$search%")->where('so.id', $owner_id)
                        ->orWhere('u.first_name', 'LIKE', "%$search%")->where('so.id', $owner_id)
                        ->orWhere('u.last_name', 'LIKE', "%$search%")->where('so.id', $owner_id)
                        ->orWhere('status.name', 'LIKE', "%$search%")->where('so.id', $owner_id);
                }
                else{
                    $query = $query->where('sa.nick', 'LIKE', "%$search%")
                        ->orWhere('so.owner', 'LIKE', "%$search%")
                        ->orWhere('sa.first_name', 'LIKE', "%$search%")
                        ->orWhere('sa.second_name', 'LIKE', "%$search%")
                        ->orWhere(DB::raw("CONCAT_WS(' ', sa.first_name, sa.second_name, sa.last_name, sa.second_last_name)"), 'LIKE', "%$search%")
                        ->orWhere(DB::raw("CONCAT_WS(' ', sa.first_name, sa.last_name, sa.second_last_name)"), 'LIKE', "%$search%")
                        ->orWhere('sa.first_name', 'LIKE', "%$search%")
                        ->orWhere('sa.second_name', 'LIKE', "%$search%")
                        ->orWhere('sa.last_name', 'LIKE', "%$search%")
                        ->orWhere('sa.second_last_name', 'LIKE', "%$search%")
                        ->orWhere('sa.access', 'LIKE', "%$search%")
                        ->orWhere('sa.password', 'LIKE', "%$search%")
                        ->orWhere(DB::raw("CONCAT_WS(' ', u.first_name, u.last_name)"), 'LIKE', "%$search%")
                        ->orWhere('u.first_name', 'LIKE', "%$search%")
                        ->orWhere('u.last_name', 'LIKE', "%$search%")
                        ->orWhere('status.name', 'LIKE', "%$search%");
                }

            }

            $recordsTotal = $query->get()->count();
            $query = $query->skip($request->start)->take($request->start + $request->length)->get();
        }
        else
        {
            $query = DB::table('satellite_accounts as sa')
                ->select('so.owner', 'sa.nick', 'sa.status_id' ,'sa.page_id', 'sp.name as page','sa.first_name','sa.second_name',
                    'sa.last_name','sa.second_last_name', 'sa.access', 'sa.password', 'sa.id AS account_id',
                    'u.first_name as user_first_name', 'u.last_name as user_last_name',
                    'status.id' ,'status.name as status_name', 'status.color as status_color', 'status.background as status_background', 'sa.updated_at')
                ->join('satellite_owners as so', 'so.id', 'sa.owner_id' )
                ->join('setting_pages as sp', 'sp.id', 'sa.page_id')
                ->leftJoin('users as u', 'u.id', 'sa.modified_by')
                ->join('satellite_accounts_status as status', 'status.id', 'sa.status_id');

            if ($owner_id != null)
            {
                $query = $query->where('so.id', $owner_id);
                //dd($query->toSql());
            }

            if ($request->status != 0)
            {
                $query = $query->where('sa.status_id', $request->status);
            }
            if ($request->page_select != 0)
            {
                $query = $query->where('sa.page_id', $request->page_select);
            }
            if ($request->page_select != 0 && $request->status != 0)
            {
                $query = $query->where('sa.page_id', $request->page_select)->where('sa.status_id', $request->status);
            }

            $recordsTotal = $query->get()->count();
            $query = $query->skip($request->start)->take($request->start + $request->length)->get();
        }



        $result[0]['owner'] = "";
        $result[0]['nick'] = "";
        $result[0]['name'] = "";
        $result[0]['access'] = "";
        $result[0]['partner'] = "";
        $result[0]['modified'] = "";
        $result[0]['status'] = "";
        $result[0]['actions'] = "";

        foreach ($query as $key => $row)
        {
            $result[$key]['owner'] = "<div><span>".$row->owner."</span></div>";
            $result[$key]['nick'] = "<div><span>".$row->nick."</span><br>
                                    <span class='small text-muted'> ".$row->page." </span></div>";
            $full_name = $row->first_name." ".$row->second_name." ".$row->last_name." ".$row->second_last_name;
            $result[$key]['name'] = "<div><span>$full_name</span></div>";
            $result[$key]['access'] = "<div><span>Email: ".chunk_split($row->access,40,"<br>")."</span>
                                <span class='small text-muted'> Clave: ".$row->password." </span>
                            </div>";
            $partner_field = "";
            $partners = SatelliteAccountPartner::select('id', 'name')->where('account_id', $row->id)->get();
            foreach ($partners as $partner) {

                $partner_field .= ($partner_field == "")? $partner->name : "<br> ".$partner->name;
            }
            if ($partner_field == "")
            {
                $result[$key]['partner'] = "<div style='cursor:pointer'>
                                <span class='badge badge-pill badge-secondary'>0</span>
                            </div>";
            }
            else
            {
                $result[$key]['partner'] = "<div style='cursor:pointer' data-toggle='tooltip' data-html='true' data-original-title='".$partner_field."'>
                                <span class='badge badge-pill badge-info'>".count($partners)."</span>
                            </div>";
            }
            $date = Carbon::parse($row->updated_at, 'UTC');
            $date = $date->isoFormat('D MMMM YYYY');
            $result[$key]['modified'] = "<div>
                                <span>".$row->user_first_name." ".$row->user_last_name."</span><br>
                                <span class='small text-muted'> ".$date." </span>
                            </div>";
            $result[$key]['status'] = "<div>
                                <span class='badge badge-pill' style='background:".$row->status_background."; color:".$row->status_color." '>".$row->status_name."</span>
                            </div>";
            if (Auth::user()->can('satellite-account-edit'))
            {
                $result[$key]['actions'] = "<button  class='btn btn-warning btn-sm' onclick='modifyAccount(".$row->account_id.")'><i class='fa fa-edit'></i></button>
                            <button class='btn btn-info btn-sm' data-target='#modal-payment-account'
                            data-toggle='modal' onclick='statisticSummary(".$row->account_id.")'><i class='fa fa-chart-line'></i></button>";
            }
            else
            {

                $result[$key]['actions'] = "<button class='btn btn-info btn-sm' data-target='#modal-payment-account'
                            data-toggle='modal' onclick='statisticSummary(".$row->id.")'><i class='fa fa-chart-line'></i></button>";
            }

        }
        return DataTables::of($query)
            ->with([
                'draw' => $request->draw,
                'recordsTotal' => $recordsTotal,
                'recordsFiltered' => $recordsTotal,
                'data' => $result,
            ])
            ->make(true);
    }

    public function buildTable()
    {

    }

    public function getOwnerAccounts(Request $request)
    {
        $accounts = SatelliteAccount::select('satellite_accounts.*', 'satellite_owners.owner', 'users.first_name as user_first_name', 'users.last_name as user_last_name')
            ->join('satellite_owners', 'satellite_owners.id', 'satellite_accounts.owner_id')
            ->join('users', 'users.id', 'satellite_accounts.modified_by')->where('satellite_accounts.owner_id', $request->owner_id)->get();

        $cont = 0;
        foreach($accounts as $key => $account){
            $result[$cont]["id"] = $account->id;
            $result[$cont]["owner"] = $account->owner;
            $result[$cont]["nick"] = $account->nick;
            $result[$cont]["page"] = $account->page->name;
            $result[$cont]["full_name"] = $account->first_name." ".$account->second_name." ".$account->last_name." ".$account->second_last_name;
            $result[$cont]["access"] = $account->access;
            $result[$cont]["password"] = $account->password;
            $result[$cont]["partners"] = SatelliteAccountPartner::select('id', 'name')->where('account_id', $account->id)->count();
            $date = Carbon::parse($account->updated_at, 'UTC');
            $date = $date->isoFormat('D MMMM YYYY');
            $result[$cont]["updated_at"] = $date;
            $result[$cont]["updated_by"] = $account->user_first_name." ".$account->user_last_name;
            $result[$cont]["status_name"] = $account->status->name;
            $result[$cont]["status_background"] = $account->status->background;
            $result[$cont]["status_color"] = $account->status->color;
            $cont++;
        }

        return response()->json($result);
    }

    public function getPagesUpload(Request $request)
    {
        $pages = SatellitePaymentPage::where('status', 1)->get();

        foreach ($pages as $key => $page) {

            $payment = SatellitePaymentFile::where('page_id', $page->id)->where('payment_date', $request->payment_date)->get();
            $pages[$key]["has_payment"] = (count($payment) > 0)? 1 : 0;
            $pages[$key]["start_date"] = (count($payment) > 0)? $payment[0]->start_date : "";
            $pages[$key]["end_date"] = (count($payment) > 0)? $payment[0]->end_date : "";
        }
        return $pages;
    }

    public function getDocuments(Request $request)
    {
        $result['rut'] = "";
        $result['chamber_commerce'] = "";
        $result['shareholder_structure'] = "";
        $result['bank_certification'] = "";
        $documents = SatelliteOwnerDocumentation::where('owner', $request->owner_id)->get();

        foreach ($documents as $key => $file)
        {
            $type = explode(".", $file->file);
            $type = $type[1];
            $type = strtolower($type);

            if ($type == "jpg" || $type == "jpeg"  || $type == "png")
            {
               $content = "<div class='col-sm-2 border border-info gallery mr-1 bg-dark' style='cursor: zoom-in; border-radius: 3px; padding: 5px; '>
                    <a href='".global_asset("../storage/app/public/".tenant('studio_slug')."/satellite/owner/$file->file")."'>
                    <img style=' height: 32px; margin-left:8px; cursor: zoom-in;' src='".asset("images/svg/image.svg")."'>
                    </a>
                </div>";
            }
            else{

                $svg = "contract.svg";
                if ($type == "csv" || $type == "xls" || $type == "xlsx")
                    $svg = "excel.svg";
                if ($type == "doc" || $type == "docx")
                    $svg = "word.svg";
                if ($type == "pdf")
                    $svg = "pdf.svg";

                $doc = '"'.$file->file.'"';
                $type = '"'.$type.'"';

                $content = "<div class='col-sm-2 border border-info mr-1 bg-dark' style='cursor: pointer; border-radius: 3px; padding: 5px; '>
                    <img onclick='embedDocuments($doc, $type)' style='height: 32px; margin-left:8px;' src='".asset("images/svg/$svg")."'></div>";
            }

            $result['rut'] .= ($file->type == 1)? $content : "";
            $result['chamber_commerce'] .= ($file->type == 2)? $content : "";
            $result['shareholder_structure'] .= ($file->type == 3)? $content : "";
            $result['bank_certification'] .= ($file->type == 4)? $content : "";

        }
        $result['rut'] = "<div class='col-lg-12 mb-2'>
                    <div class='row'>
                        ".$result['rut']."
                    </div>
                </div>";

        $result['chamber_commerce'] = "<div class='col-lg-12 mb-2'>
                    <div class='row'>
                        ".$result['chamber_commerce']."
                    </div>
                </div>";

        $result['shareholder_structure'] = "<div class='col-lg-12 mb-2'>
                    <div class='row'>
                        ".$result['shareholder_structure']."
                    </div>
                </div>";

        $result['bank_certification'] = "<div class='col-lg-12 mb-2'>
                    <div class='row'>
                        ".$result['bank_certification']."
                    </div>
                </div>";

        return $result;
    }

    public function getCommissions(Request $request)
    {
        $result = "<table class='table table-hover'>
                    <thead>
                        <tr>
                            <th>Propietario</th>
                            <th>% Comision</th>
                            <th>Tipo</th>
                            <th>Pagina</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>";
        $commissions = SatelliteOwnerCommissionRelation::where('owner_giver', $request->owner_id)->get();

        foreach ($commissions as $commission)
        {
            $json = $commission;
            $result .= "<tr>";
            $result .= "<td>".$commission->ownerReceiver->owner."</td>";
            $result .= "<td>".$commission->percent."</td>";
            $type = ($commission->type == 1)? "Todas las paginas" : (($commission->type == 2)? "Solo esta pagina" : "Todas excepto esta");
            $result .= "<td>".$type."</td>";
            $page = ($commission->page > 0)? $commission->settingPage->name : "";
            $result .= "<td>".$page."</td>";
            if ($commission->ownerGiver->status == 1)
            {
                $edit = (Auth::user()->can('satellite-owner-percent-edit'))? "<i class='fa fa-edit text-warning mr-2' data-toggle='modal' data-target='#modal-update-commission' onclick='modalUpdate($json)' style='cursor:pointer'></i>" : "";
                $delete = (Auth::user()->can('satellite-owner-percent-delete'))? "<i class='fa fa-trash-alt text-danger' onclick='removeCommission($commission->id)' style='cursor:pointer'></i>" : "";

                $result .= "<td>
                $edit
                $delete
                </td>";
            }
            else
            {
                $result .= "<td>
                <i class='fa fa-edit text-warning mr-2' style='cursor:no-drop'></i>
                <i class='fa fa-trash-alt text-danger' style='cursor:no-drop'></i>
                </td>";
            }

            $result .= "</tr>";

        }
        $result .= "</tbody></table>";

        return $result;
    }

    public function getLogs(Request $request)
    {
        $result = "<table class='table table-hover table-striped' id='table-logs'>
                    <thead>
                        <tr>
                            <th>Tipo</th>
                            <th>Accion</th>
                            <th>Antes</th>
                            <th>Despues</th>
                            <th>Modificado Por</th>
                            <th>Fecha</th>
                        </tr>
                    </thead>
                    <tbody>";
        $logs = SatelliteAccountLog::where('account_id', $request->account_id)->orderBy('id', 'DESC')->get();

        foreach ($logs as $log)
        {
            $result .= "<tr>";

            $text = "text-success";
            if ($log->action == "modificado")
                $text = "text-warning";
            if ($log->action == "eliminado")
                $text = "text-danger";

            $result .= "<td>".$log->type."</td>";
            $result .= "<td class='".$text."'>".$log->action."</td>";
            $result .= "<td style='width:25%'>".$log->previous."</td>";
            $result .= "<td style='width:25%'>".$log->now."</td>";
            $result .= "<td>".$log->created_by_user->first_name." ".$log->created_by_user->last_name."</td>";
            $date = Carbon::parse($log->created_at, 'UTC');
            $date = $date->isoFormat('D MMMM YYYY H:mm');
            $result .= "<td>".$date."</td>";
            $result .= "</tr>";

        }
        $result .= "</tbody></table>";

        return $result;
    }

    public function getNotes(Request $request)
    {
        $result = "<table class='table table-hover table-striped' id='table-notes'>
                    <thead>
                        <tr>
                            <th>Descripcion</th>
                            <th>Registrado Por</th>
                            <th>Fecha</th>
                        </tr>
                    </thead>
                    <tbody>";
        $notes = SatelliteAccountNote::where('account_id', $request->account_id)->orderBy('id', 'DESC')->get();

        foreach ($notes as $note)
        {
            $result .= "<tr>";
            $result .= "<td style='width:58%'>".$note->note."</td>";
            $result .= "<td>".$note->created_by_user->first_name." ".$note->created_by_user->last_name."</td>";
            $date = Carbon::parse($note->created_at, 'UTC');
            $date = $date->isoFormat('D MMMM YYYY H:mm');
            $result .= "<td>".$date."</td>";
            $result .= "</tr>";

        }
        $result .= "</tbody></table>";

        return $result;
    }

    public function getCities(Request $request)
    {
    	$data = DB::table('global_cities')->select('id', 'name')->where('department_id',$request->department)->orderBy('name', 'asc')->get();
        return response()->json($data);
    }

    public function getPayments(Request $request)
    {
        $payment_accounts = SatellitePaymentAccount::select('satellite_payment_accounts.id','satellite_payment_accounts.nick','satellite_payment_accounts.amount','so.owner','sp.name', 'satellite_payment_accounts.owner_id')
        ->leftjoin('satellite_owners as so', 'so.id', 'satellite_payment_accounts.owner_id')
        ->join('setting_pages as sp', 'sp.id', 'satellite_payment_accounts.page_id')
        ->where('payment_date', $request->payment_date)->orderBy('so.owner', 'asc')->orderBy('sp.name', 'asc')->orderBy('satellite_payment_accounts.nick', 'asc')->get();

        $file_trm = SatellitePaymentFile::select('trm')->where('payment_date', $request->payment_date)->first();
        $not_matching = SatellitePaymentAccount::where('payment_date', $request->payment_date)->where('owner_id', null)->count();

        return response()->json([
            "payment_accounts" => $payment_accounts,
            "trm" => $file_trm['trm'],
            "not_matching" => $not_matching,
        ]);
    }

    public function getPaymentDates(Request $request)
    {
        $payment_dates = SatellitePaymentFile::select('payment_date')->distinct('payment_date')->orderBy('payment_date', 'desc')->get();
        return response()->json([
            "payment_dates" => $payment_dates,
        ]);
    }

    public function getAcocountingDates()
    {
        $dates = SatellitePaymentPayroll::select('payment_date')->distinct('payment_date')->orderBy('payment_date', 'DESC')->get();
        return response()->json($dates);
    }

    public function getOwnerPayments(Request $request)
    {
        $payments = SatellitePaymentAccount::select('so.owner', 'satellite_payment_accounts.owner_id', DB::raw('SUM(satellite_payment_accounts.amount) as total'))
        ->leftjoin('satellite_owners as so', 'so.id', 'satellite_payment_accounts.owner_id')
        ->where('payment_date', $request->payment_date)->orderBy('so.owner', 'asc')
        ->groupBy('satellite_payment_accounts.owner_id')
        ->get();

        return response()->json($payments);
    }

    public function getOwnerPaymentDates(Request $request)
    {
        $account_dates = SatellitePaymentAccount::select('payment_date')->where('owner_id', $request->owner_id)->distinct('payment_date')
            ->orderBy('payment_date', 'desc');
        $final_dates = SatellitePaymentPayroll::select('payment_date')->where('owner_id', $request->owner_id)->distinct('payment_date')
            ->union($account_dates)->orderBy('payment_date', 'desc')->get();

        return response()->json($final_dates);
    }

    public function getOwnerPaymentPayroll(Request $request)
    {
        $payroll = false;
        $accumulated = 0;
        $payment_payroll = SatellitePaymentPayroll::with(['globalBank', 'globalDocument', 'paymentMethods'])->where('owner_id', $request->owner_id)
            ->where('payment_date', $request->payment_date)->get();
        if (count($payment_payroll) > 0) {
            $payroll = true;
        }
        else{
            $last_payroll = SatellitePaymentPayroll::select('payment_date')->where('owner_id', $request->owner_id)->where('payment_date', '<' ,$request->payment_date)->orderBy('payment_date', 'desc')->first();

            if ($last_payroll == null) {
                $accumulated =  SatellitePaymentAccount::where('owner_id', $request->owner_id)->where('payment_date', '<=' ,$request->payment_date)->sum('amount');
            }
            else{
                $accumulated = SatellitePaymentAccount::where('owner_id', $request->owner_id)->where('payment_date', '<=' ,$request->payment_date)->where('payment_date', '>' ,$last_payroll->payment_date)->sum('amount');
            }

        }

        $last_payment_date = $this->verifyIfLastPaymentDate($request->payment_date);

        return response()->json([
            "payment_payroll" => $payment_payroll,
            "payroll" => $payroll,
            "accumulated" => round($accumulated, 2),
            "last_payment_date" => $last_payment_date,
        ]);
    }

    public function getOwnerPaymentPayrolls(Request $request)
    {
        $payrolls = SatellitePaymentPayroll::where('owner_id', $request->owner_id)->orderBy('payment_date', 'DESC')->get();
        return response()->json([
            "payrolls" => $payrolls,
        ]);
    }

    public function getOwnerDetails(Request $request)
    {
        $result["earnings"] = SatellitePaymentPayroll::where('owner_id', $request->owner_id)->sum('percent_gb_pesos');
        $result["retention"] = SatellitePaymentPayroll::where('owner_id', $request->owner_id)->sum('retention');
        $result["boutique"] = SatellitePaymentDeduction::where('owner_id', $request->owner_id)->where('type', 3)->sum('amount');
        $result["status_boutique"] = 0;
        $result["cafeteria"] = SatellitePaymentDeduction::where('owner_id', $request->owner_id)->where('type', 2)->sum('amount');
        $result["loans"] = 0;
        $result["total_dollar"] = SatellitePaymentDeduction::where('owner_id', $request->owner_id)->where('deduction_to', 0)
        ->orWhere('owner_id', $request->owner_id)->where('deduction_to', 1)
        ->sum('amount');
        $result["total_pesos"] = SatellitePaymentDeduction::where('owner_id', $request->owner_id)->where('deduction_to', 2)->sum('amount');

        return response()->json([$result]);
    }

    public function getPayDeductions(Request $request)
    {
        $result = [];
        $paydeductions = SatellitePaymentPayDeduction::where('deduction_id', $request->deduction_id)->orderBy('id', 'desc')->get();
        foreach ($paydeductions as $key => $paydeduction) {
            $result[$key] = $paydeduction;
            $result[$key]["user"] = $paydeduction->created_by_user->first_name." ".$paydeduction->created_by_user->last_name;
        }
        return response()->json(["paydeductions" => $result]);
    }

    public function getOwnerPaymentAccounts(Request $request)
    {
        $payment_accounts = [];
        $cont = 0;
        $payment_payroll = SatellitePaymentPayroll::where('owner_id', $request->owner_id)->where('payment_date', $request->payment_date)->get();
        if (count($payment_payroll) > 0) {
            $distinct_dates = SatellitePaymentAccount::select('payment_date')->distinct('payment_date')->where('payroll_id', $payment_payroll[0]->id)
                ->orderBy('payment_date', 'desc')->get();
        }
        else{
            $last_payroll = SatellitePaymentPayroll::select('payment_date')->where('owner_id', $request->owner_id)
                ->where('payment_date', '<' ,$request->payment_date)->orderBy('payment_date', 'desc')->first();
            if ($last_payroll == null) {
                $distinct_dates = SatellitePaymentAccount::select('payment_date')->distinct('payment_date')->where('owner_id', $request->owner_id)
                    ->where('payment_date', '<=' ,$request->payment_date)->orderBy('payment_date', 'desc')->get();
            }
            else{
                $distinct_dates = SatellitePaymentAccount::select('payment_date')->distinct('payment_date')->where('owner_id', $request->owner_id)
                    ->where('payment_date', '<=' ,$request->payment_date)->where('payment_date', '>' ,$last_payroll->payment_date)->orderBy('payment_date', 'desc')->get();
            }
        }

        foreach ($distinct_dates as $distinct) {
            $payment_accounts[$cont]["payment_date"] = $distinct->payment_date;
            $resume = SatellitePaymentAccount::where('payment_date', $distinct->payment_date)->where('owner_id', $request->owner_id)->orderBy('payment_date', 'desc')->get();

            for ($i=0; $i < count($resume) ; $i++) {

                $description = $resume[$i]->description;
                $description_xlove = "";

                if($resume[$i]->page_id == 9){
                    $exploded = explode("-->", $resume[$i]->description);
                    $description = $exploded[0];
                    if (count($exploded) > 1)
                    {
                        $description_xlove = $exploded[1];
                    }

                }
                $payment_accounts[$cont]["resume"][$i] = [
                    "page" => $resume[$i]->page->name,
                    "nick" => $resume[$i]->nick,
                    "amount" => $resume[$i]->amount,
                    "description" => $description,
                    "description_xlove" => $description_xlove,
                ];
            }

            $cont++;
        }

        return response()->json([
            "payment_accounts" => $payment_accounts,
        ]);
    }

    public function getNotAssignedCommission(Request $request)
    {
        $result = SatellitePaymentCommission::where('owner_id', $request->owner_id)->where('payroll_id', null)->get();
        return response()->json($result);
    }

    public function getAssignedCommission(Request $request)
    {
        $result = SatellitePaymentCommission::where('owner_id', $request->owner_id)->where('payroll_id', $request->payroll_id)->where('payroll_id', "!=", null)->get();
        return response()->json($result);
    }

    public function getOwnerDeductions(Request $request)
    {
        if ($request->payment_date == "")
        {
            $result = SatellitePaymentDeduction::where('owner_id', $request->owner_id)->where('status', 0)
            ->get();
        }
        else
        {
            $result = SatellitePaymentDeduction::
            where([
                ['owner_id', $request->owner_id],
                ['payment_date', null]
            ])
            ->orWhere([
                ['owner_id', $request->owner_id],
                ['payment_date', '<=' , $request->payment_date ],
                ['finished_date', '>=' , $request->payment_date ],
                ['status', 1],
            ])
            ->orWhere([
                ['owner_id', $request->owner_id],
                ['payment_date', '<=' , $request->payment_date ],
                ['finished_date', null ],
                ['status', 0],
            ])
            ->get();
        }
        return response()->json($result);
    }

    public function generatePayment()
    {
        return view("adminModules.satellite.payment.generate");
    }

    public function getPayrollDates(Request $request)
    {
        $result = SatellitePaymentPayroll::select('payment_date')->distinct('payment_date')->where('is_user', $request->is_user)
            ->orderBy('payment_date', 'desc')->get();
        return response()->json($result);
    }

    public function getPayrolls(Request $request)
    {
        $result = SatellitePaymentPayroll::with(['globalBank', 'globalDocument', 'paymentMethods', 'owner'])
            ->where('payment_date', $request->payment_date)->where('is_user', $request->is_user)
            ->orderBy('payment_methods_id', 'asc')->orderBy('bank', 'asc')->get();
        $last_payment_date = $this->verifyIfLastPaymentDate($request->payment_date);

        foreach ($result as $key => $res){
            $deductions_amount = SatellitePaymentDeduction::where('owner_id', $res->owner_id)->sum('amount');
            $exist_commission = SatellitePaymentCommission::where('owner_id', $res->owner_id)->where('payroll_id', null)->exists();
            $result[$key]['has_payment_deduction'] = ($deductions_amount > 0 || $exist_commission)? 1 : 0;
            $last_pay = SatellitePaymentPayDeduction::select('payment_date')->where('owner_id', $res->owner_id)->orderBy('payment_date', 'desc')->first();
            $result[$key]['last_pay'] = ($last_pay != null)? $last_pay->payment_date : "";
        }
        return response()->json([
            "payrolls" => $result,
            "last_payment_date" => $last_payment_date,
        ]);
    }

    public function getDebts(Request $request)
    {
        $result = [];
        $debts = SatellitePaymentDeduction::select('owner_id')->distinct('owner_id')->where('amount', '>', 0)->get();
        $cont = 0;
        foreach($debts as $key => $debt){
            if ($request->is_user == $debt->owner->is_user){
                $result[$cont]["owner_id"] = $debt->owner_id;
                $result[$cont]["user_id"] = $debt->owner->user_id;
                $result[$cont]["owner"] = $debt->owner->owner;
                $result[$cont]["boutique"] = SatellitePaymentDeduction::where('deduction_to', 2)->where('owner_id', $debt->owner_id)->where('type', 3)->sum('amount');
                $result[$cont]["pesos"] = SatellitePaymentDeduction::where('deduction_to', 2)->where('owner_id', $debt->owner_id)->sum('amount');
                $result[$cont]["dolares"] = SatellitePaymentDeduction::where('deduction_to', 0)->where('owner_id', $debt->owner_id)
                    ->orWhere('deduction_to', 1)->where('owner_id', $debt->owner_id)->sum('amount');
                $blocked = BoutiqueBlockedUser::where('user_id', $debt->owner->user_id)->exists();
                $result[$cont]["blocked"] = ($request->is_user == 0)? "" : $blocked;
                $cont++;
            }
        }

        return response()->json($result);
    }

    public function getAccumulations(Request $request)
    {
        $result = [];
        $payments = SatellitePaymentAccount::select('so.owner', 'satellite_payment_accounts.owner_id', DB::raw('SUM(satellite_payment_accounts.amount) as total'))
            ->join('satellite_owners as so', 'so.id', 'satellite_payment_accounts.owner_id')
            ->where('payroll_id', null)->where('so.is_user', $request->is_user)->where('so.status', 1)->orderBy('so.owner', 'asc')
            ->groupBy('satellite_payment_accounts.owner_id')
            ->get();
        $cont = 0;
        foreach($payments as $payment){
            $result[$cont]["owner_id"] = $payment->owner_id;
            $result[$cont]["owner"] = $payment->owner;
            $result[$cont]["amount"] = $payment->total;
            $cont++;
        }

        return response()->json($result);
    }

    public function getEarnings(Request $request)
    {
        if ($request->year == "Todos") {
            $start_date = "2019-01-01";
            $end_date = date("Y")."-12-31";
        }
        else{
            $start_date = $request->year."-01-01";
            $end_date = $request->year."-12-31";
        }

        $result = [];
        $owners = SatellitePaymentPayroll::select('owner_id')->where('is_user', $request->is_user)->whereBetween('payment_date', [$start_date, $end_date])->groupBy('owner_id')
            ->orderByRaw('SUM(percent_studio) DESC')->get();
        $cont = 0;
        foreach($owners as $owner){
            $result[$cont]["owner"] = $owner->owner->owner;
            $result[$cont]["amount"] = SatellitePaymentPayroll::where('owner_id', $owner->owner_id)
                ->whereBetween('payment_date', [$start_date, $end_date])->sum('percent_studio');
            $result[$cont]["count_payrolls"] = SatellitePaymentPayroll::where('owner_id', $owner->owner_id)
                ->whereBetween('payment_date', [$start_date, $end_date])->count('id');
            $last_payroll = SatellitePaymentPayroll::select('payment_date')->where('owner_id', $owner->owner_id)
                ->whereBetween('payment_date', [$start_date, $end_date])->orderBy('payment_date', 'desc')->first();
            $result[$cont]["average"] = $result[$cont]["amount"] / $result[$cont]["count_payrolls"];
            $percent = SatellitePaymentPayroll::select('percent')->where('owner_id', $owner->owner_id)
                ->whereBetween('payment_date', [$start_date, $end_date])->orderBy('payment_date', 'desc')->first();
            $result[$cont]["percent_gb"] = 100 - $percent->percent;
            $result[$cont]["earnings_gb"] = SatellitePaymentPayroll::where('owner_id', $owner->owner_id)
                ->whereBetween('payment_date', [$start_date, $end_date])->sum('percent_gb_pesos');
            $result[$cont]["last_payroll"] = $last_payroll->payment_date;
            $result[$cont]["city"] = ($owner->owner->city != null)? $owner->owner->city->name : "";
            $cont++;
        }

        return response()->json($result);
    }

    public function getStatisticEmail()
    {
        $result = SatelliteTemplateStatistic::first();
        if(file_exists(public_path("storage/" . tenant('studio_slug') . "/logo/logo.png") )){
            $result["logo"] = "<img src='".asset("storage/" . tenant('studio_slug') . "/logo/logo.png")."' style='height: 40px'>";
        }
        else{
            $result["logo"] = "<h1 style='color: #2553ff'>".tenant('studio_name')."</h1>";
        }
        return response()->json($result);
    }

    public function listPayments()
    {
        $user_permission = Auth()->user()->getPermissionsViaRoles()->pluck('name');
        return view("adminModules.satellite.payment.list")->with(compact(['user_permission']));
    }

    public function listOwners()
    {
        $user_permission = Auth()->user()->getPermissionsViaRoles()->pluck('name');
        return view("adminModules.satellite.owner.list")->with(compact(['user_permission']));
    }

    public function listUsers()
    {
        $documents = SatelliteUsersDocumentsType::select('id', 'name')->get();
        $countries = DB::table('global_countries')->select('id', 'name', 'code')->get();
        return view("adminModules.satellite.user.list")->with([
            "documents" => $documents,
            "countries" => $countries,
        ]);
    }

    public function listTemplates()
    {
        $templates = SatelliteTemplatesType::select('id', 'name')->get();
        return view("adminModules.satellite.template.list")->with([
            "templates" => $templates,
        ]);
    }

    public function payrollsExport(Request $request)
    {

        $result['sin_fp'] = SatellitePaymentPayroll::select('so.owner','satellite_payment_payroll.payment')->join('satellite_owners as so', 'so.id',
            'satellite_payment_payroll.owner_id')
            ->where('payment_date', $request->payment_date)->where('satellite_payment_payroll.is_user', $request->is_user)
            ->where('satellite_payment_payroll.payment_methods_id', 1)
            ->orderBy('payment_methods_id', 'asc')->get()->toArray();

        $result = $this->payrollBancolombia($result, $request);
        //$result = $this->payrollGrupoAval($result, $request);
        $result = $this->payrollEfecty($result, $request);
        $result = $this->payrollPaxum($result, $request);
        $result = $this->payrollCheque($result, $request);
        $result = $this->payrollBancoUsa($result, $request);
        $result = $this->payrollWesternUnion($result, $request);
        $result = $this->payrollBancolombiaNew($result, $request);
        //$result = $this->payrollGrupoAvalNewSheet($result, $request);

        if ($request->export_type == 1){
            $result = $this->payrollBancoomevaOthersSheet($result, $request);
            $result = $this->payrollBancolombiaOthers($result, $request);
            $result = $this->payrollAVVillasOthers($result, $request);
            return Excel::download(new WithAVVillas($result, $request->payment_date), 'Generar Pago(' . $request->payment_date . ').xlsx');
        }
        if ($request->export_type == 2){
            $result = $this->payrollBancolombiaOthers($result, $request);
            return Excel::download(new WithBancolombia($result, $request->payment_date), 'Generar Pago(' . $request->payment_date . ').xlsx');
        }
        if ($request->export_type == 3){
            $result = $this->payrollBancoomevaOthersSheet($result, $request);
            return Excel::download(new WithBancoomeva($result, $request->payment_date), 'Generar Pago(' . $request->payment_date . ').xlsx');
        }
    }

    public function payrollBancolombia($result, $request)
    {
        $result['bancolombia'] = [];
        $bancolombia = SatellitePaymentPayroll::where('payment_date', $request->payment_date)->where('is_user', $request->is_user)->where('bank', 21)->where('first_time', 0)
            ->orWhere('payment_date', $request->payment_date)->where('is_user', $request->is_user)->where('bank', 36)->where('first_time', 0)
            ->orderBy('holder', 'asc')->get();
        $reference = "L000000";
        foreach ($bancolombia as $key => $value)
        {
            $increment = explode("L",$reference);
            $increment = $increment[1] + 1 ;
            $count = strlen($increment);
            $count = 6 - $count;
            for ($i=0; $i < $count ; $i++) {
                $increment = "0".$increment;
            }
            $reference = "L".$increment;
            $result['bancolombia'][$key]["owner"] = $value->owner->owner;
            $result['bancolombia'][$key]["document_type"] = ($value->document_type == 4)? 5 : $value->document_type;
            $result['bancolombia'][$key]["document_number"] = $value->document_number;
            $holder = trim($value->holder);
            $holder = str_replace("ñ", "n", $holder);
            $holder = str_replace("Ñ", "N", $holder);
            $holder = str_replace(".", "", $holder);
            $holder = str_replace("&", "y", $holder);
            $result['bancolombia'][$key]["holder"] = substr($holder, 0, 30);
            $result['bancolombia'][$key]["transaction_type"] = ($value->account_type == 2)? 27 : 37;
            $result['bancolombia'][$key]["bank_code"] = ($value->bank == 21)? 1007 : 1507;
            $result['bancolombia'][$key]["account_number"] = $value->account_number;
            $result['bancolombia'][$key]["email"] = "";
            $result['bancolombia'][$key]["authorized_document"] = "";
            $result['bancolombia'][$key]["reference"] = $reference;
            $result['bancolombia'][$key]["ofice"] = "";
            $result['bancolombia'][$key]["payment"] = $value->payment;
        }
        return $result;
    }

    public function payrollBancolombiaNew($result, $request)
    {
        $result['bancolombia_new'] = [];
        $bancolombia_new = SatellitePaymentPayroll::where('payment_date', $request->payment_date)->where('is_user', $request->is_user)
            ->where('bank', 21)->where('first_time', 1)
            ->orWhere('payment_date', $request->payment_date)->where('is_user', $request->is_user)->where('bank', 36)->where('first_time', 1)
            ->orderBy('holder', 'asc')->get();
        $reference = "L000000";
        foreach ($bancolombia_new as $key => $value)
        {
            $increment = explode("L",$reference);
            $increment = $increment[1] + 1 ;
            $count = strlen($increment);
            $count = 6 - $count;
            for ($i=0; $i < $count ; $i++) {
                $increment = "0".$increment;
            }
            $reference = "L".$increment;
            $result['bancolombia_new'][$key]["owner"] = $value->owner->owner;
            $result['bancolombia_new'][$key]["document_type"] = ($value->document_type == 4)? 5 : $value->document_type;
            $result['bancolombia_new'][$key]["document_number"] = $value->document_number;
            $holder = trim($value->holder);
            $holder = str_replace("ñ", "n", $holder);
            $holder = str_replace("Ñ", "N", $holder);
            $holder = str_replace(".", "", $holder);
            $holder = str_replace("&", "y", $holder);
            $result['bancolombia_new'][$key]["holder"] = substr($holder, 0, 30);
            $result['bancolombia_new'][$key]["transaction_type"] = ($value->account_type == 2)? 27 : 37;
            $result['bancolombia_new'][$key]["bank_code"] = ($value->bank == 21)? 1007 : 1507;
            $result['bancolombia_new'][$key]["account_number"] = $value->account_number;
            $result['bancolombia_new'][$key]["email"] = "";
            $result['bancolombia_new'][$key]["authorized_document"] = "";
            $result['bancolombia_new'][$key]["reference"] = $reference;
            $result['bancolombia_new'][$key]["ofice"] = "";
            $result['bancolombia_new'][$key]["payment"] = $value->payment;
        }
        return $result;
    }

    public function payrollBancolombiaOthers($result, $request)
    {
        $result['bancolombia_others'] = [];
        //esta consulta se usaba para cuando existia grupo aval
        /*$bancolombia_others = SatellitePaymentPayroll::where('payment_date', $request->payment_date)->where('is_user', $request->is_user)->where('payment_methods_id', 2)
            ->where('bank', "!=" , 2)->where('bank', "!=" , 9)->where('bank', "!=" , 11)->where('bank', "!=" , 17)->where('bank', "!=" , 21)->where('bank', "!=" , 36)
            ->orWhere('payment_date', $request->payment_date)->where('is_user', $request->is_user)->where('payment_methods_id', 6)
            ->where('bank', "!=" , 2)->where('bank', "!=" , 9)->where('bank', "!=" , 11)->where('bank', "!=" , 17)->where('bank', "!=" , 21)->where('bank', "!=" , 36)
            ->orWhere('payment_date', $request->payment_date)->where('is_user', $request->is_user)->where('payment_methods_id', 9)
            ->where('bank', "!=" , 2)->where('bank', "!=" , 9)->where('bank', "!=" , 11)->where('bank', "!=" , 17)->where('bank', "!=" , 21)->where('bank', "!=" , 36)
            ->orderBy('holder', 'asc')->get();*/

        $bancolombia_others = SatellitePaymentPayroll::where('payment_date', $request->payment_date)->where('is_user', $request->is_user)->where('payment_methods_id', 2)
            ->where('bank', "!=" , 21)->where('bank', "!=" , 36)
            ->orWhere('payment_date', $request->payment_date)->where('is_user', $request->is_user)->where('payment_methods_id', 6)
            ->where('bank', "!=" , 21)->where('bank', "!=" , 36)
            ->orWhere('payment_date', $request->payment_date)->where('is_user', $request->is_user)->where('payment_methods_id', 9)
            ->where('bank', "!=" , 21)->where('bank', "!=" , 36)
            ->orderBy('holder', 'asc')->get();
        $reference = "L000000";
        foreach ($bancolombia_others as $key => $value)
        {
            $increment = explode("L",$reference);
            $increment = $increment[1] + 1 ;
            $count = strlen($increment);
            $count = 6 - $count;
            for ($i=0; $i < $count ; $i++) {
                $increment = "0".$increment;
            }
            $reference = "L".$increment;
            $result['bancolombia_others'][$key]["owner"] = $value->owner->owner;
            $result['bancolombia_others'][$key]["document_type"] = ($value->document_type == 4)? 5 : $value->document_type;
            $result['bancolombia_others'][$key]["document_number"] = trim($value->document_number);
            $holder = trim($value->holder);
            $holder = str_replace("ñ", "n", $holder);
            $holder = str_replace("Ñ", "N", $holder);
            $holder = str_replace(".", "", $holder);
            $holder = str_replace("&", "y", $holder);
            $result['bancolombia_others'][$key]["holder"] = substr($holder, 0, 30);
            $result['bancolombia_others'][$key]["transaction_type"] = ($value->account_type == 2)? 27 : 37;
            $result['bancolombia_others'][$key]["bank_code"] = $value->globalBank->code;
            $result['bancolombia_others'][$key]["account_number"] = trim($value->account_number);
            $result['bancolombia_others'][$key]["email"] = "";
            $result['bancolombia_others'][$key]["authorized_document"] = "";
            $result['bancolombia_others'][$key]["reference"] = $reference;
            $result['bancolombia_others'][$key]["ofice"] = "";
            $result['bancolombia_others'][$key]["payment"] = $value->payment;
        }
        return $result;
    }

    /*public function payrollGrupoAval($result, $request)
    {
        $result['grupo_aval'] = [];
        $grupo_aval = SatellitePaymentPayroll::where('payment_date', $request->payment_date)->where('is_user', $request->is_user)->where('bank', 2)->where('first_time', 0)
            ->orWhere('payment_date', $request->payment_date)->where('is_user', $request->is_user)->where('bank', 9)->where('first_time', 0)
            ->orWhere('payment_date', $request->payment_date)->where('is_user', $request->is_user)->where('bank', 11)->where('first_time', 0)
            ->orWhere('payment_date', $request->payment_date)->where('is_user', $request->is_user)->where('bank', 17)->where('first_time', 0)
            ->orderBy('holder', 'asc')->get();

        foreach ($grupo_aval as $key => $value)
        {
            $result['grupo_aval'][$key]["owner"] = $value->owner->owner;
            $result['grupo_aval'][$key]["document_number"] = $value->document_number;
            if($value->globalDocument != null)
            {
                $document_type = explode(" ", $value->globalDocument->name);
                $document_type = $document_type[0];
            }
            else
            {
                $document_type = "vacio";
            }

            $result['grupo_aval'][$key]["document_type"] = $document_type;
            $result['grupo_aval'][$key]["holder"] = $value->holder;
            if ($value->bank == 2){
                $bank = "AVVILLAS";
            }
            if ($value->bank == 9){
                $bank = "BOGOTA";
            }
            if ($value->bank == 11){
                $bank = "OCCIDENTE";
            }
            if ($value->bank == 17){
                $bank = "POPULAR";
            }
            $result['grupo_aval'][$key]["bank"] = $bank;
            $result['grupo_aval'][$key]["account_type"] = ($value->account_type == 1)? "AHORROS" : "CORRIENTE";
            $result['grupo_aval'][$key]["account_number"] = $value->account_number;
            $result['grupo_aval'][$key]["email"] = "";
            $result['grupo_aval'][$key]["payment"] = $value->payment;
        }
        return $result;
    }*/

    /*public function payrollGrupoAvalNewSheet($result, $request)
    {
        $result['grupo_aval_new'] = [];
        $grupo_aval_new = SatellitePaymentPayroll::where('payment_date', $request->payment_date)->where('is_user', $request->is_user)->where('bank', 2)->where('first_time', 1)
            ->orWhere('payment_date', $request->payment_date)->where('is_user', $request->is_user)->where('bank', 9)->where('first_time', 1)
            ->orWhere('payment_date', $request->payment_date)->where('is_user', $request->is_user)->where('bank', 11)->where('first_time', 1)
            ->orWhere('payment_date', $request->payment_date)->where('is_user', $request->is_user)->where('bank', 17)->where('first_time', 1)
            ->orderBy('holder', 'asc')->get();

        foreach ($grupo_aval_new as $key => $value)
        {
            $result['grupo_aval_new'][$key]["owner"] = $value->owner->owner;
            $result['grupo_aval_new'][$key]["document_number"] = $value->document_number;
            $document_type = explode(" ", $value->globalDocument->name);
            $document_type = $document_type[0];
            $result['grupo_aval_new'][$key]["document_type"] = $document_type;
            $result['grupo_aval_new'][$key]["holder"] = $value->holder;
            if ($value->bank == 2){
                $bank = "AVVILLAS";
            }
            if ($value->bank == 9){
                $bank = "BOGOTA";
            }
            if ($value->bank == 11){
                $bank = "OCCIDENTE";
            }
            if ($value->bank == 17){
                $bank = "POPULAR";
            }
            $result['grupo_aval_new'][$key]["bank"] = $bank;
            $result['grupo_aval_new'][$key]["account_type"] = ($value->account_type == 1)? "AHORROS" : "CORRIENTE";
            $result['grupo_aval_new'][$key]["account_number"] = $value->account_number;
            $result['grupo_aval_new'][$key]["email"] = "";
            $result['grupo_aval_new'][$key]["payment"] = $value->payment;
        }
        return $result;
    }*/

    public function payrollEfecty($result, $request)
    {
        $result['efecty'] = [];
        $efecty = SatellitePaymentPayroll::where('payment_date', $request->payment_date)->where('is_user', $request->is_user)->where('payment_methods_id', 3)
            ->orderBy('holder', 'asc')->get();

        foreach ($efecty as $key => $value)
        {
            $result['efecty'][$key]["owner"] = $value->owner->owner;
            $result['efecty'][$key]["payment"] = $value->payment;
            $result['efecty'][$key]["payment_method"] = "Efecty";
            $holder = trim($value->holder);
            $holder = str_replace("ñ", "n", $holder);
            $holder = str_replace("Ñ", "N", $holder);
            $holder = str_replace(".", "", $holder);
            $holder = str_replace("&", "y", $holder);
            $result['efecty'][$key]["holder"] = $holder;
            $result['efecty'][$key]["document_type"] = ($value->document_type == 5)? $value->globalDocument->name : ($value->document_type != null ?
                $value->globalDocument->name_simplified : "");
            $result['efecty'][$key]["document_number"] = $value->document_number;
            $result['efecty'][$key]["address"] = $value->address;
            $result['efecty'][$key]["city"] = $value->city_id;
            $result['efecty'][$key]["phone"] = $value->phone;
        }
        return $result;
    }

    public function payrollPaxum($result, $request)
    {
        $result['paxum'] = [];
        $paxum = SatellitePaymentPayroll::where('payment_date', $request->payment_date)->where('is_user', $request->is_user)->where('payment_methods_id', 4)
            ->orderBy('holder', 'asc')->get();
        $reference = "L000000";
        foreach ($paxum as $key => $value)
        {
            $increment = explode("L",$reference);
            $increment = $increment[1] + 1 ;
            $count = strlen($increment);
            $count = 6 - $count;
            for ($i=0; $i < $count ; $i++) {
                $increment = "0".$increment;
            }
            $reference = "L".$increment;
            $result['paxum'][$key]["owner"] = $value->owner->owner;
            $result['paxum'][$key]["format"] = $value->holder.",".$value->percent_studio.",USD,".$value->payment_range.",".$reference;
        }
        return $result;
    }

    public function payrollCheque($result, $request)
    {
        $result['cheque'] = [];
        $cheque = SatellitePaymentPayroll::where('payment_date', $request->payment_date)->where('is_user', $request->is_user)->where('payment_methods_id', 5)
            ->orderBy('holder', 'asc')->get();

        foreach ($cheque as $key => $value)
        {
            $result['cheque'][$key]["owner"] = $value->owner->owner;
            $result['cheque'][$key]["payment"] = $value->payment;
            $result['cheque'][$key]["payment_method"] = "Cheque sin Retencion";
            $holder = trim($value->holder);
            $holder = str_replace("ñ", "n", $holder);
            $holder = str_replace("Ñ", "N", $holder);
            $holder = str_replace(".", "", $holder);
            $holder = str_replace("&", "y", $holder);
            $result['cheque'][$key]["holder"] = $holder;
            $result['cheque'][$key]["document_number"] = $value->document_number;
            $result['cheque'][$key]["address"] = $value->address;
            $result['cheque'][$key]["city"] = $value->city_id;
            $result['cheque'][$key]["phone"] = $value->phone;
        }
        return $result;
    }

    public function payrollBancoUsa($result, $request)
    {
        $result['usa'] = [];
        $usa = SatellitePaymentPayroll::where('payment_date', $request->payment_date)->where('is_user', $request->is_user)->where('payment_methods_id', 7)
            ->orderBy('holder', 'asc')->get();

        foreach ($usa as $key => $value)
        {
            $result['usa'][$key]["owner"] = $value->owner->owner;
            $result['usa'][$key]["payment"] = $value->percent_studio;
            $result['usa'][$key]["payment_method"] = "Banco USA sin Retencion";
            $result['usa'][$key]["bank"] = $value->bank_usa;
            $holder = trim($value->holder);
            $holder = str_replace("ñ", "n", $holder);
            $holder = str_replace("Ñ", "N", $holder);
            $holder = str_replace(".", "", $holder);
            $holder = str_replace("&", "y", $holder);
            $result['usa'][$key]["holder"] = $holder;
            $result['usa'][$key]["document_type"] = $value->document_type != null ? $value->globalDocument->name_simplified : "";
            $result['usa'][$key]["document_number"] = $value->document_number;
            $result['usa'][$key]["account_number"] = $value->account_number;
            $result['usa'][$key]["account_type"] = ($value->account_type == 1)? "AHORROS" : "CORRIENTE";
            $result['usa'][$key]["city_id"] = $value->city_id;
        }
        return $result;
    }

    public function payrollWesternUnion($result, $request)
    {
        $result['western_union'] = [];
        $western_union = SatellitePaymentPayroll::where('payment_date', $request->payment_date)->where('is_user', $request->is_user)->where('payment_methods_id', 8)
            ->orderBy('holder', 'asc')->get();

        foreach ($western_union as $key => $value)
        {
            $result['western_union'][$key]["owner"] = $value->owner->owner;
            $result['western_union'][$key]["document_type"] = $value->globalDocument->name;
            $result['western_union'][$key]["document_number"] = $value->document_number;
            $holder = trim($value->holder);
            $holder = str_replace("ñ", "n", $holder);
            $holder = str_replace("Ñ", "N", $holder);
            $holder = str_replace(".", "", $holder);
            $holder = str_replace("&", "y", $holder);
            $result['western_union'][$key]["holder"] = $holder;
            $result['western_union'][$key]["address"] = $value->address;
            $result['western_union'][$key]["city_id"] = $value->city_id;
            $result['western_union'][$key]["country"] = $value->country;
            $result['western_union'][$key]["phone"] = $value->phone;
            $result['western_union'][$key]["payment"] = $value->payment;
        }
        return $result;
    }

    public function payrollAVVillasOthers($result, $request)
    {
        $result['avvillas'] = [];
        //esta consulta se usaba para cuando existia grupo aval
        /*$avvillas = SatellitePaymentPayroll::where('payment_date', $request->payment_date)->where('is_user', $request->is_user)->where('payment_methods_id', 2)
            ->where('bank', "!=" , 2)->where('bank', "!=" , 9)->where('bank', "!=" , 11)->where('bank', "!=" , 17)->where('bank', "!=" , 21)->where('bank', "!=" , 36)
            ->orWhere('payment_date', $request->payment_date)->where('is_user', $request->is_user)->where('payment_methods_id', 6)
                ->where('bank', "!=" , 2)->where('bank', "!=" , 9)->where('bank', "!=" , 11)->where('bank', "!=" , 17)->where('bank', "!=" , 21)->where('bank', "!=" , 36)
            ->orWhere('payment_date', $request->payment_date)->where('is_user', $request->is_user)->where('payment_methods_id', 9)
            ->where('bank', "!=" , 2)->where('bank', "!=" , 9)->where('bank', "!=" , 11)->where('bank', "!=" , 17)->where('bank', "!=" , 21)->where('bank', "!=" , 36)
            ->orderBy('holder', 'asc')->get();*/

        $avvillas = SatellitePaymentPayroll::where('payment_date', $request->payment_date)->where('is_user', $request->is_user)->where('payment_methods_id', 2)
            ->where('bank', "!=" , 21)->where('bank', "!=" , 36)
            ->orWhere('payment_date', $request->payment_date)->where('is_user', $request->is_user)->where('payment_methods_id', 6)
            ->where('bank', "!=" , 21)->where('bank', "!=" , 36)
            ->orWhere('payment_date', $request->payment_date)->where('is_user', $request->is_user)->where('payment_methods_id', 9)
            ->where('bank', "!=" , 21)->where('bank', "!=" , 36)
            ->orderBy('holder', 'asc')->get();

        foreach ($avvillas as $key => $value)
        {
            $holder = trim($value->holder);
            $holder = substr($holder, 0,22);
            $holder = explode(" ",$holder);
            $str = $holder[0];
            if (isset($holder[1])) {
                $str = $str." ".$holder[1];
            }
            $holder = $str;
            $holder = str_replace("ñ", "n", $holder);
            $holder = str_replace("Ñ", "N", $holder);
            $holder = str_replace(".", "", $holder);
            $holder = str_replace("&", "y", $holder);

            $result['avvillas'][$key]["holder"] = $holder;
            if ($value->document_type == 1){
                $document_type = "CEDULA DE CIUDADANIA";
            }
            if ($value->document_type == 2){
                $document_type = "CEDULA DE EXTRANJERIA";
            }
            if ($value->document_type == 3){
                $document_type = "NIT  ";
            }
            if ($value->document_type == 4){
                $document_type = "PASAPORTE NACIONAL";
            }
            if ($value->document_type == 5){
                $document_type = "CEDULA VENEZOLANA";
            }
            $result['avvillas'][$key]["document_type"] = trim($document_type);
            $result['avvillas'][$key]["document_number"] = trim($value->document_number);
            $result['avvillas'][$key]["account_type"] = ($value->account_type == 1)? "AHORROS" : "CORRIENTE";
            $result['avvillas'][$key]["account_number"] = trim($value->account_number);
            $result['avvillas'][$key]["payment"] = trim($value->payment);
            $bank = $value->globalBank->name;
            $bank = trim($bank);
            if ($value->bank == 3){
                $bank = "BBVA COLOMBIA ";
            }
            if ($value->bank == 8){
                $bank = "BANCO DAVIVIENDA S.A. ";
            }
            if ($value->bank == 5 || $value->bank == 37){
                $bank = "RED MULTIBANCA COLPATRIA S.A. ";
            }
            if ($value->bank == 9){
                $bank = "BANCO DE BOGOTÁ ";
            }
            if ($value->bank == 4){
                $bank = "BANCO CAJA SOCIAL ";
            }
            if ($value->bank == 34){
                $bank = "ITAU - CORPBANCA";
            }
            if ($value->bank == 2){
                $bank = "BANCO AV VILLAS ";
            }
            if ($value->bank == 29){
                $bank = "BANCO DAVIPLATA";
            }
            $result['avvillas'][$key]["bank"] = $bank;
            $result['avvillas'][$key]["city"] = "CALI";
            $result['avvillas'][$key]["transaction_type"] = ($value->account_type == 2)? "CREDITO  A CC                                      " : "CREDITO  A AH                                      ";
            $result['avvillas'][$key]["date"] = date("d")."/".date("m")."/".date("Y");
            $result['avvillas'][$key]["period"] = "";
            $result['avvillas'][$key]["reference"] = "";
            $result['avvillas'][$key]["description"] = "";
            $result['avvillas'][$key]["active"] = "SI";

        }
        return $result;
    }

    public function payrollBancoomevaOthersSheet($result, $request)
    {
        $result['bancoomeva_others'] = [];
        $bancoomeva_others = SatellitePaymentPayroll::where('payment_date', $request->payment_date)->where('is_user', $request->is_user)->where('payment_methods_id', 2)
            ->where('bank', "!=" , 2)->where('bank', "!=" , 9)->where('bank', "!=" , 11)->where('bank', "!=" , 17)->where('bank', "!=" , 21)->where('bank', "!=" , 36)
            ->orWhere('payment_date', $request->payment_date)->where('is_user', $request->is_user)->where('payment_methods_id', 6)
            ->where('bank', "!=" , 2)->where('bank', "!=" , 9)->where('bank', "!=" , 11)->where('bank', "!=" , 17)->where('bank', "!=" , 21)->where('bank', "!=" , 36)
            ->orWhere('payment_date', $request->payment_date)->where('is_user', $request->is_user)->where('payment_methods_id', 9)
            ->where('bank', "!=" , 2)->where('bank', "!=" , 9)->where('bank', "!=" , 11)->where('bank', "!=" , 17)->where('bank', "!=" , 21)->where('bank', "!=" , 36)
            ->orderBy('holder', 'asc')->get();

        foreach ($bancoomeva_others as $key => $value)
        {
            $result['bancoomeva_others'][$key]["owner"] = $value->owner->owner;
            $result['bancoomeva_others'][$key]["origin"] = "901145956";
            $result['bancoomeva_others'][$key]["account_origin"] = "'010904706206";
            $holder = trim($value->holder);
            $holder = str_replace("ñ", "n", $holder);
            $holder = str_replace("Ñ", "N", $holder);
            $holder = str_replace(".", "", $holder);
            $holder = str_replace("&", "y", $holder);
            $result['bancoomeva_others'][$key]["holder"] = substr($holder, 0, 30);
            $result['bancoomeva_others'][$key]["document_number"] = $value->document_number;
            $result['bancoomeva_others'][$key]["payment"] = $value->payment;
            $result['bancoomeva_others'][$key]["concept"] = "Concepto de Pago";
            $result['bancoomeva_others'][$key]["bank_code"] = $value->globalBank->code;
            $result['bancoomeva_others'][$key]["account_type"] = $value->account_type;
            $result['bancoomeva_others'][$key]["account_number"] = $value->account_number;
        }
        return $result;
    }

    public function pdfContract(Request $request)
    {
            $tenant = Tenant::find(1);
            $contracts = $tenant->run(function () use ($tenant, $request) {
            date_default_timezone_set("America/Bogota");
            $year = date("Y");
            $mes = date("m");
            $dia = date("d");
            $meses = array("01"=>'Enero',"02"=>'Febrero',"03"=>'Marzo',"04"=>'Abril',"05"=>'Mayo',"06"=>'Junio',"07"=>'Julio',"08"=>'Agosto',"09"=>'Septiembre',"10"=>'Octubre',"11"=>'Noviembre',"12"=>'Diciembre');
            $contract = SatelliteContract::find($request->id);
            $sc_cont = $contract->increase;
            $contract->increase += 1;
            $contract->save();

            //$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

            TCPDF::SetCreator("PDF_CREATOR");
            TCPDF::SetAuthor('GBMEDIA');
            TCPDF::SetTitle('Contrato');
            TCPDF::SetSubject('Contrato');
            TCPDF::setPrintHeader(false);
            TCPDF::setPrintFooter(false);
            TCPDF::SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
            TCPDF::SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_RIGHT);
            TCPDF::SetAutoPageBreak(TRUE, 0);
            TCPDF::setImageScale(PDF_IMAGE_SCALE_RATIO);

//1ra pagina
            TCPDF::AddPage();
            TCPDF::setJPEGQuality(75);
            $tbl = <<<EOD
<table style="border: 1px solid #B7BAB7; text-align: center; padding: 5px 0"  nobr="true">
 <tr>
  <td style="width: 25%; padding: 10px;"><img width="150px" src="images/default/logoiva.jpg"></td>
  <td style="width: 50%; border: 1px solid #B7BAB7; font-size:10px; text-align: center">CONTRATO DE MANDATO <br>
De acuerdo por las disposiciones del Código de Comercio y del Código Civil, en especial las señaladas en la Ley 964 de 2005, la Ley 27 de 1990, el Decreto 2555 de 2010, el Decreto 3960 de 2010 y las normas que en futuro las modifiquen o adicionen
 </td>
 <td style="width: 25%; border: 1px solid #B7BAB7; font-size:10px; text-align: center">pagina 1 de 8</td>
 </tr>
</table>
EOD;
            TCPDF::writeHTML($tbl, true, false, false, false, '');

            if ($sc_cont <= 9)
                $numero = "000".$sc_cont;
            if ($sc_cont > 9 && $sc_cont <= 99)
                $numero = "00".$sc_cont;
            if ($sc_cont > 99 && $sc_cont <= 999)
                $numero = "0".$sc_cont;
            if ($sc_cont >= 1000)
                $numero = $sc_cont;

            TCPDF::SetFont('helvetica', 'B', 12);
            TCPDF::SetXY(60, 20);
            TCPDF::Write(50, 'CONTRATO DE MANDATO NÚMERO  ', '', 0, 'L', true, 0, false, false, 0);
            TCPDF::SetXY(135, 20);
            TCPDF::Write(50, $numero, '', 0, 'L', true, 0, false, false, 0);

            TCPDF::SetFont('helvetica', 'B', 11);
            TCPDF::SetXY(40, 25);
            $tbl = <<<EOD
            <span>ENTRE GB MEDIA GROUP S.A.S. Y $contract->company / $contract->studio_name</span>
EOD;
            TCPDF::writeHTML($tbl, true, false, false, false, '');

            TCPDF::SetFont('helvetica', 'B', 9);
            TCPDF::SetXY(15, 55);
            $tbl = <<<EOD
            <table style="border: 1px solid #B7BAB7; text-align: center; padding: 2px 0"  nobr="true">
             <tr>
              <td colspan="2" rowspan="" headers="" style="background-color:#1E9A87">Mandante</td>

             </tr>
             <tr>
              <td style="border: 1px solid #B7BAB7">Razon Social</td>
              <td style="border: 1px solid #B7BAB7">$contract->company</td>
             </tr>
              <tr>
              <td style="border: 1px solid #B7BAB7">NIT</td>
              <td style="border: 1px solid #B7BAB7">$contract->nit</td>
             </tr>
             <tr>
              <td style="border: 1px solid #B7BAB7">REPRESENTANTE LEGAL</td>
              <td style="border: 1px solid #B7BAB7">$contract->holder</td>
             </tr>
             <tr>
              <td style="border: 1px solid #B7BAB7">N° DE IDENTIFICACIÓN</td>
              <td style="border: 1px solid #B7BAB7">$contract->card_id</td>
             </tr><tr>
              <td style="border: 1px solid #B7BAB7">DIRECCIÓN DE NOTIFICACIÓN</td>
              <td style="border: 1px solid #B7BAB7">$contract->address  $contract->city  $contract->department</td>
             </tr><tr>
              <td style="border: 1px solid #B7BAB7">TELÉFONOS DE CONTACTO</td>
              <td style="border: 1px solid #B7BAB7">$contract->phone</td>
             </tr><tr>
              <td style="border: 1px solid #B7BAB7">CORREO ELECTRÓNICO</td>
              <td style="border: 1px solid #B7BAB7">$contract->email</td>
             </tr><tr>
              <td colspan="2" rowspan="" headers="" style="background-color:#1E9A87">Mandatario</td>
             </tr><tr>
              <td style="border: 1px solid #B7BAB7">EMPRESA</td>
              <td style="border: 1px solid #B7BAB7">GB MEDIA GROUP S.A.S.</td>
             </tr><tr>
              <td style="border: 1px solid #B7BAB7">NIT</td>
              <td style="border: 1px solid #B7BAB7">901.145.956-7</td>
             </tr><tr>
              <td style="border: 1px solid #B7BAB7">REPRESENTANTE LEGAL</td>
              <td style="border: 1px solid #B7BAB7">RICHARD ANDRÉS BEDOYA JOSEPH </td>
             </tr><tr>
              <td style="border: 1px solid #B7BAB7">N° DE IDENTIFICACIÓN</td>
              <td style="border: 1px solid #B7BAB7">C.C 94.474.531</td>
             </tr><tr>
              <td style="border: 1px solid #B7BAB7">DIRECCIÓN DE NOTIFICACIÓN</td>
              <td style="border: 1px solid #B7BAB7">CL 5 No. 39 – 119 Barrio Tequendama CALI - VALLE</td>
             </tr><tr>
              <td style="border: 1px solid #B7BAB7">TELÉFONOS DE CONTACTO</td>
              <td style="border: 1px solid #B7BAB7">(+57) 0323865120 Cel. 3009109951</td>
             </tr><tr>
              <td style="border: 1px solid #B7BAB7">CORREO ELECTRÓNICO</td>
              <td style="border: 1px solid #B7BAB7">info@grupo-bedoya.com</td>
             </tr>
            </table>
EOD;
            TCPDF::writeHTML($tbl, true, false, false, false, '');

            TCPDF::SetXY(20, 150);
            TCPDF::SetFont('helvetica',"" ,11);
            if ($contract->company_type == "Persona Natural") {
                $actuando = "";
            }
            else
            {
                $actuando = 'o en su calidad de representante legal de la sociedad <span style="font-weight: bold">'.$contract->company.' ('.$contract->studio_name.')</span>, identificada con <span style="font-weight: bold">NIT '.$contract->nit.'</span>,';
            }
            $tbl = '<p style="text-align: justify;">
                Entre los suscritos <span style="font-weight: bold">'.$contract->holder.'</span>, identificado con cédula de ciudadanía No. <span style="font-weight: bold">'
                .$contract->card_id.'</span>, actuando en nombre propio '.$actuando.' domiciliad@ en la ciudad de <span style="font-weight: bold">'.$contract->city.' – '
                .$contract->department.' en '.$contract->address.' </span> y teléfonos de contacto: <span style="font-weight: bold">'.$contract->phone.'</span>, quien para todos los efectos del presente contrato se denominará <span style="font-weight: bold">EL MANDANTE</span>, y de otra parte <span style="font-weight: bold">RICHARD ANDRÉS BEDOYA JOSEPH</span>, mayor de edad, identificado con cédula de ciudadanía No. 94.474.531, actuando en su calidad de representante legal principal de la sociedad <span style="font-weight: bold">GB MEDIA GROUP S.A.S.</span>, empresa legalmente constituida en Colombia, con domicilio principal en la ciudad de Cali – Valle, identificada con <span style="font-weight: bold">NIT. 901.145.956-7</span>, propietaria de la marca “<span style="font-weight: bold">GRUPO BEDOYA</span>” cuyo objeto social es la prestación de servicios de intermediación comercial internacional, marketing, estudios publicitarios, desarrollos web, diseños de páginas web, soporte técnico y virtual, operación de redes sociales así como actividades de capacitación o eventos de formación de personas, así como la representación e intermediación para la exportación de servicios para mayores de dieciocho (18) años, quien para todos los efectos del presente contrato se denominará <span style="font-weight: bold">EL MANDATARIO</span>, ambos comerciantes que aceptan y certifican que están vinculados al entretenimiento web para adultos y que al gestionar redes sociales interactivas en vivo y páginas de entretenimiento para adultos de contenido erótico, utilizando personal mayores de dieciocho (18) años, quienes previamente han presentado la respectiva documentación y que desarrollan dichas actividades de una manera libre y voluntaria. <span style="font-weight: bold">EL MANDATARIO</span> por medio de este contrato de forma detallada describe los diferentes servicios, beneficios y costos, así como las obligaciones y derechos que tiene <span style="font-weight: bold">EL MANDATARIO</span>, todo lo anterior regido por las disposiciones
            </p>';
            TCPDF::writeHTML($tbl, true, false, true, false, '');

            TCPDF::SetFont('helvetica',"" ,10);
            TCPDF::SetXY(40, 270);
            $tbl = <<<EOD
            <span>
                DIRECCIÓN: CL 5 No. 39 – 119 Barrio Tequendama / CALI – COLOMBIA</span>
EOD;
            TCPDF::writeHTML($tbl, true, false, false, false, '');

            TCPDF::SetFont('helvetica',"" ,9);
            TCPDF::SetXY(40, 275);
            $tbl = <<<EOD
            <span>
                Tel. (+57) 0323865120 Cel. 3009109951 – (+57) 3227487143 (solo chat)</span>
EOD;
            TCPDF::writeHTML($tbl, true, false, false, false, '');

            TCPDF::SetFont('helvetica',"" ,9);
            TCPDF::SetXY(40, 280);
            $tbl = <<<EOD
            <span>
                Página web: www.grupo-bedoya.com / email: info@grupo-bedoya.com</span>
EOD;
            TCPDF::writeHTML($tbl, true, false, false, false, '');

            //2da pagina
            TCPDF::AddPage();
            TCPDF::setJPEGQuality(75);
            $tbl = <<<EOD
            <table style="border: 1px solid #B7BAB7; text-align: center; padding: 5px 0"  nobr="true">
             <tr>
              <td><img src="images/default/logoiva.jpg"  width="280" height="50"></td>
              <td style="border: 1px solid #B7BAB7; font-size:10px; text-align: center">CONTRATO DE MANDATO <br>
            De acuerdo por las disposiciones del Código de Comercio y del Código Civil, en especial las señaladas en la Ley 964 de 2005, la Ley 27 de 1990, el Decreto 2555 de 2010, el Decreto 3960 de 2010 y las normas que en futuro las modifiquen o adicionen
             </td>
             <td style="width: 25%; border: 1px solid #B7BAB7; font-size:10px; text-align: center">pagina 2 de 8</td>
             </tr>

            </table>
EOD;
            TCPDF::writeHTML($tbl, true, false, false, false, '');

            $sc_xc2 = 100 - $contract->percent;

            TCPDF::SetFont('helvetica',"" ,11);
            $tbl = '<p style="text-align: justify;">
            del Código Civil y Código de Comercio que versan sobre la materia, en especial a las señaladas en la Ley novecientos sesenta y cuatro (964) de dos mil cinco (2005); la ley veintisiete (27) de mil novecientos noventa (1990), el decreto dos mil quinientos cincuenta y cinco (2555) de dos mil diez (2010), el decreto tres mil novecientos sesenta (3960) de dos mil diez (2010) y las normas que en el futuro las modifiquen o adicionen.
            <br><br>
            <span style="font-weight: bold">PRIMERA: OBJETO DEL CONTRATO: EL MANDANTE</span> faculta a <span style="font-weight: bold">EL MANDATARIO</span> para recibir, custodiar, administrar y entregar los recursos originados por la exportación de servicios de modelaje webcam prestados por parte de <span style="font-weight: bold">EL MANDANTE</span> y sus contratistas, entre otras a las siguientes empresas (Páginas Web) con domicilio por fuera del territorio colombiano (Estados Unidos de Norteamérica, Hungría, Chipre, Luxemburgo, Republica Checa y otros países de Europa): LiveJasmin, Streamate, Camsoda, Chaturbate, Imlive, Bongacams, StripChat, Cams, Flirt4Free, XLoveCams, Firecams, Cam4, SkyPrivate, y cualquier otra Página de Webcams que pudiere existir, las cuales trabajará de forma exclusiva para/con <span style="font-weight: bold">EL MANDATARIO</span>, esto en el entendido como la obligación que tiene <span style="font-weight: bold">EL MANDANTE</span> de trabajar las páginas mencionadas y/o otras existentes bajo las directrices de <span style="font-weight: bold">EL MANDATARIO</span>, motivo por el cual queda establecido que le es expresamente prohibido a <span style="font-weight: bold">EL MANDANTE</span> percibir ingresos por otros terceros, intermediarios o recibir recurso alguno por parte de las páginas (Empresas Webcam), so pena de incurrir en las multas establecidas en la cláusula penal y la terminación anticipada del presente contrato. Por lo anterior, <span style="font-weight: bold">EL MANDATARIO</span> queda facultado para representar para todos los efectos a <span style="font-weight: bold">EL MANDANTE</span>, esto ante el sector financiero nacional e internacional, canalizando las divisas objeto de la prestación de servicios al exterior por parte de <span style="font-weight: bold">EL MANDANTE</span>, a través del mercado cambiario, especialmente ante el Banco de la República, entidades vigiladas por la Superintendencia Financiera de Colombia y la Unidad de Información y Análisis Financiero - UIAF.
            <br><br>
            <span style="font-weight: bold">EL MANDATARIO</span> en virtud de garantizar la transparencia objeto del presente contrato se compromete a reportar ante el BANCO DE LA REPÚBLICA todos los ingresos originados por la exportación de servicios de <span style="font-weight: bold">EL MANDANTE</span> y reportar ante las entidades financieras y fiscales de la República de Colombia el porcentaje que le corresponde a <span style="font-weight: bold">EL MANDANTE</span> como ingresos recibidos para terceros.
            <br><br>
            <span style="font-weight: bold">SEGUNDA: VALOR DEL CONTRATO Y FORMA DE PAGO:</span> Por medio del presente contrato, <span style="font-weight: bold">EL MANDATARIO</span> se compromete a canalizar el 100% de los ingresos mensuales producto de los servicios de exportación prestados por <span style="font-weight: bold">EL MANDANTE</span> y trasladarle a este o a la persona autorizada expresamente por él, el '.$contract->percent.'% de estos ingresos semanalmente, motivo por el cual la retribución económica que recibirá <span style="font-weight: bold">EL MANDATARIO</span> por la administración de los mismos es del '.$sc_xc2.'% de estos recursos. Las utilidades y/o perdidas que resulten del ejercicio comercial serán asumidas de manera independientes por cada una de las partes, por lo cual se acuerdan la no existencia de solidaridad de una parte frente a la otra. De la misma manera se deja claro que <span style="font-weight: bold">EL MANDATARIO</span> por ser persona jurídica agente de retención en la fuente, se encuentra en la obligación de practicar la respectiva retención en la fuente de acuerdo al artículo 73 de la Ley 2010 de 2019:
            </p>';
            TCPDF::writeHTML($tbl, true, false, false, false, '');

            TCPDF::SetFont('helvetica',"" ,10);
            TCPDF::SetXY(40, 270);
            $tbl = <<<EOD
            <span>
                DIRECCIÓN: CL 5 No. 39 – 119 Barrio Tequendama / CALI – COLOMBIA</span>
EOD;
            TCPDF::writeHTML($tbl, true, false, false, false, '');

            TCPDF::SetFont('helvetica',"" ,9);
            TCPDF::SetXY(40, 275);
            $tbl = <<<EOD
            <span>
              Tel. (+57) 0323865120 Cel. 3009109951 – (+57) 3227487143 (solo chat)</span>
EOD;
            TCPDF::writeHTML($tbl, true, false, false, false, '');

            TCPDF::SetFont('helvetica',"" ,9);
            TCPDF::SetXY(40, 280);
            $tbl = <<<EOD
            <span>
              Página web: www.grupo-bedoya.com / email: info@grupo-bedoya.com</span>
EOD;
            TCPDF::writeHTML($tbl, true, false, false, false, '');

            //3ra pagina
            TCPDF::AddPage();
            TCPDF::setJPEGQuality(75);
            $tbl = <<<EOD
            <table style="border: 1px solid #B7BAB7; text-align: center; padding: 5px 0"  nobr="true">
             <tr>
              <td><img src="images/default/logoiva.jpg"  width="280" height="50"></td>
              <td style="border: 1px solid #B7BAB7; font-size:10px; text-align: center">CONTRATO DE MANDATO <br>
            De acuerdo por las disposiciones del Código de Comercio y del Código Civil, en especial las señaladas en la Ley 964 de 2005, la Ley 27 de 1990, el Decreto 2555 de 2010, el Decreto 3960 de 2010 y las normas que en futuro las modifiquen o adicionen
             </td>
             <td style="width: 25%; border: 1px solid #B7BAB7; font-size:10px; text-align: center">pagina 3 de 8</td>
             </tr>

            </table>
EOD;
            TCPDF::writeHTML($tbl, true, false, false, false, '');

            $sc_xc2 = 100 - $contract->percent;

            TCPDF::SetFont('helvetica',"" ,11);
            $tbl = '<p style="text-align: justify;">
            <span style="font-weight: bold">ARTÍCULO 73.</span> Adicionese un párrafo al articulo 368 el Estatuto Tributario, el cual quedaría así:
            <br>
                  <span><span style="font-weight: bold">PARÁGRAFO 3</span> Entiéndase también como agentes de retención las personas jurídicas y naturales exportadoras de servicios de entretenimiento para adultos a través del sistema webcam, que mediante contrato de mandato como hecho generador practiquen la retención en la fuente por servicios al mandante en el respectivo pago o abono en cuenta, de conformidad con el artículo 392 del Estatuto Tributario. Estas empresas estarán organizadas en una Federación de Comercio Electrónico para Adultos para su control y el sector será reglamentado mediante ley.</span>
            </p>';
            TCPDF::writeHTML($tbl, true, false, false, false, '');

            TCPDF::SetFont('helvetica',"" ,11);
            $tbl = '<p style="text-align: justify;">
            <br><br>
            <span style="font-weight: bold">TERCERA DURACION DEL CONTRATO:</span> El termino mínimo del presente contrato será de cinco (05) años. Si <span style="font-weight: bold">EL MANDANTE</span> o <span style="font-weight: bold">EL MANDATARIO</span> 30 días calendario antes del vencimiento no presentan ningún tipo de objeción por escrito este se renovará automáticamente por un término de igual duración (5 años).

            <br><br>
            <span style="font-weight: bold">CUARTA OBLIGACIONES DE EL MANDATARIO: EL MANDATARIO</span> se obliga a responder ante <span style="font-weight: bold">El MANDANTE</span> por las actividades encomendadas dentro del objeto del presente contrato y en especial las siguientes:
            <br>
            A.  Realizar pagos semanales dentro de los tres (03) días hábiles de cada semana, de los ingresos percibidos para <span style="font-weight: bold">EL MANDANTE</span> mediante las siguientes opciones a elección de <span style="font-weight: bold">EL MANDANTE:</span> '.$contract->payment_method.'.
            <br>
            B. Apoyar el proceso de capacitación a modelos, monitores y personal administrativo y directivo de
            acuerdo a las políticas establecidas por <span style="font-weight: bold">EL MANDATARIO</span>, para lo cual brindará capacitaciones y conferencias que se deben establecer de común acuerdo entre las partes, ya sea local o virtual, esto teniendo en cuenta el cronograma de trabajo establecido previamente, la disponibilidad por parte de <span style="font-weight: bold">EL MANDATARIO</span> y los costos de desplazamiento y demás viáticos.
            <br>
            C.  Realizar creación de cuentas y perfiles en las diferentes páginas de video transmisión erótica, administración y soporte de las mismas cuando en dichas cuentas se presenten novedades.
            <br>
            D.  Brindar soporte administrativo, legal y tributario para consultas específicas del negocio o referir a los profesionales competentes para tal fin.
            <br>
            E. Asesoría y acompañamiento en el diseño de interiores respecto a las instalaciones de transmisión, así como la asesoría técnica en compra de equipos, programas, actualizaciones e
            instalación, según disponibilidad.
            <br>
            F. Ofrecer incentivos/apoyos financieros a <span style="font-weight: bold">EL MANDANTE</span> a título de mutuo o préstamos sin ningún tipo de interés de acuerdo a políticas internas de <span style="font-weight: bold">EL MANDATARIO</span> y la disponibilidad de recursos de este.
            <br><br>
            <span style="font-weight: bold">QUINTA: OBLIGACIONES DE EL MANDANTE.</span> Se obliga a:
            <br>
            A. Cumplir diligentemente las obligaciones a su cargo que se puedan generar
              tanto de este contrato como de los contratos que suscriba <span style="font-weight: bold">EL MANDATARIO.</span>
            </p>
            ';
            TCPDF::writeHTML($tbl, true, false, false, false, '');


            TCPDF::SetFont('helvetica',"" ,10);
            TCPDF::SetXY(40, 270);
            $tbl = <<<EOD
            <span>
                DIRECCIÓN: CL 5 No. 39 – 119 Barrio Tequendama / CALI – COLOMBIA</span>
EOD;
            TCPDF::writeHTML($tbl, true, false, false, false, '');

            TCPDF::SetFont('helvetica',"" ,9);
            TCPDF::SetXY(40, 275);
            $tbl = <<<EOD
            <span>
              Tel. (+57) 0323865120 Cel. 3009109951 – (+57) 3227487143 (solo chat)</span>
EOD;
            TCPDF::writeHTML($tbl, true, false, false, false, '');

            TCPDF::SetFont('helvetica',"" ,9);
            TCPDF::SetXY(40, 280);
            $tbl = <<<EOD
            <span>
              Página web: www.grupo-bedoya.com / email: info@grupo-bedoya.com</span>
EOD;
            TCPDF::writeHTML($tbl, true, false, false, false, '');

            //4ta pagina
            TCPDF::AddPage();
            TCPDF::setJPEGQuality(75);
            $tbl = <<<EOD
            <table style="border: 1px solid #B7BAB7; text-align: center; padding: 5px 0"  nobr="true">
             <tr>
              <td><img src="images/default/logoiva.jpg"  width="280" height="50"></td>
              <td style="border: 1px solid #B7BAB7; font-size:10px; text-align: center">CONTRATO DE MANDATO <br>
            De acuerdo por las disposiciones del Código de Comercio y del Código Civil, en especial las señaladas en la Ley 964 de 2005, la Ley 27 de 1990, el Decreto 2555 de 2010, el Decreto 3960 de 2010 y las normas que en futuro las modifiquen o adicionen
             </td>
             <td style="width: 25%; border: 1px solid #B7BAB7; font-size:10px; text-align: center">pagina 4 de 8</td>
             </tr>

            </table>
EOD;
            TCPDF::writeHTML($tbl, true, false, false, false, '');

            TCPDF::SetFont('helvetica',"" ,11);
            $tbl = '<p style="text-align: justify;">
            B. Valorar y aceptar, con un criterio responsable y diligente, las recomendaciones, sugerencias y asesorías que le sean presentadas por <span style="font-weight: bold">EL MANDATARIO</span> en ejecución de este contrato.
            <br>
            C. <span style="font-weight: bold">EL MANDANTE</span> acepta desde ya el descuento en porcentaje que realizara <span style="font-weight: bold">EL MANDATARIO</span> de los ingresos percibidos de las páginas/empresas mencionadas en el objeto del contrato.
            <br>
            D. No permitir el ingreso a menores de edad a las instalaciones donde funcione el estudio y mantener prácticas administrativas de acuerdo a lo establecido por <span style="font-weight: bold">EL MANDATARIO</span>.
            <br>
            E.  Cumplir diligentemente y velar por que sus dependientes también lo hagan en cuanto a las reglas y normas establecidas en cada una de las páginas webcam a trabajar.
            <br>
            F. Pagar todos los honorarios y gastos de cualquier naturaleza, incluyendo los prestacionales, en el evento en el que sean aplicables, de todo el personal que puede <span style="font-weight: bold">EL MANDANTE</span> en cualquier momento contratar, aun verbalmente.
            <br>
            G. Pagar todos los gastos y costos, sean de honorarios o laborales, todos los relacionados con seguridad social, incluyendo, sin limitarse a, afiliación a una Empresa Promotora de salud, al sistema de pensiones, al sistema de riesgos laborales y demás que puedan aplicar, de todo el personal de índole administrativo y artístico que <span style="font-weight: bold">EL MANDANTE</span> decida contratar para el manejo de sus asuntos internos.
            <br>
            H. Garantizar la promoción y transmisión de sus modelos de manera personal, certificando que no presentará documentación alterada o inexacta y que se compromete a NO reusar cuentas creadas por <span style="font-weight: bold">EL MANDATARIO</span> y que pertenezcan a otras modelos inactivas o retiradas de <span style="font-weight: bold">EL MANDANTE</span>.
            <br>
            I. Será el encargado de la correcta contratación de sus modelos para la transmisión en vivo de las plataformas de entretenimiento para adultos, lo cual incluye la suscripción de los respectivos contratos, la obligatoriedad de la inscripción en el Registro Único Tributario – RUT, el correcto pago de la seguridad social y la bancarización de los recursos que estos perciban.
            <br>
            J. Garantizar con un criterio responsable y dentro de las buenas conductas legales, que tanto sus modelos como las instalaciones de transmisión cumplan con las todas las normas sanitarias y de bioseguridad para llevar a cabo las transmisiones.
            <br>
            K. Permitir que el equipo de calidad de <span style="font-weight: bold">EL MANDATARIO</span> acceda en cualquier momento y sin previo aviso a la transmisión en vivo de sus modelos, por lo cual autoriza desde ahora y de forma irrevocable por el tiempo que dure este contrato a cualquier funcionario de <span style="font-weight: bold">EL MANDANTARIO</span> para realizar el respectivo monitoreo, control de calidad y seguimiento, esto para garantizar la calidad de sus transmisiones y de esta manera garantizar a las plataformas internacionales el cumplimiento de normas y políticas de transmisión, como lo son la transmisión de personal mayor de dieciocho (18) años, así como verificar que <span style="font-weight: bold">EL MANDANTE</span> esté trabajando exclusivamente con las páginas descritas en el artículo primero y que son objeto del presente contrato.
            <br>
            L. <span style="font-weight: bold">EL MANDANTE</span> no podrá promocionar páginas dentro de otras páginas a las que <span style="font-weight: bold">EL MANDATARIO</span> este afiliado, pues esto será considerado como competencia desleal y por tal motivo serán canceladas dichas cuentas de forma inmediata, lo que hará que la página NO reconozca el valor total facturado y por lo tanto será <span style="font-weight: bold">EL MANDANTE</span> quien deberán asumir dichos pagos (Esto también aplica para casos de fraude, o cualquier caso que la Pagina considere fraude o retenga el dinero).
            <br>
            M. <span style="font-weight: bold">EL MANDANTE</span> comprende y acepta que es la única persona autorizada para solicitar la creación de cuentas y perfiles de sus modelos y por lo tanto en ningún caso permitirá la promoción o actuación de alguien más en nombre de <span style="font-weight: bold">EL MANDANTE</span> o sus modelos, por lo cual le queda
            </p>';
            TCPDF::writeHTML($tbl, true, false, false, false, '');


            TCPDF::SetFont('helvetica',"" ,10);
            TCPDF::SetXY(40, 270);
            $tbl = <<<EOD
            <span>
                DIRECCIÓN: CL 5 No. 39 – 119 Barrio Tequendama / CALI – COLOMBIA</span>
EOD;
            TCPDF::writeHTML($tbl, true, false, false, false, '');

            TCPDF::SetFont('helvetica',"" ,9);
            TCPDF::SetXY(40, 275);
            $tbl = <<<EOD
            <span>
              Tel. (+57) 0323865120 Cel. 3009109951 – (+57) 3227487143 (solo chat)</span>
EOD;
            TCPDF::writeHTML($tbl, true, false, false, false, '');

            TCPDF::SetFont('helvetica',"" ,9);
            TCPDF::SetXY(40, 280);
            $tbl = <<<EOD
            <span>
              Página web: www.grupo-bedoya.com / email: info@grupo-bedoya.com</span>
EOD;
            TCPDF::writeHTML($tbl, true, false, false, false, '');

            //5ta pagina
            TCPDF::AddPage();
            TCPDF::setJPEGQuality(75);
            $tbl = <<<EOD
            <table style="border: 1px solid #B7BAB7; text-align: center; padding: 5px 0"  nobr="true">
             <tr>
              <td><img src="images/default/logoiva.jpg"  width="280" height="50"></td>
              <td style="border: 1px solid #B7BAB7; font-size:10px; text-align: center">CONTRATO DE MANDATO <br>
            De acuerdo por las disposiciones del Código de Comercio y del Código Civil, en especial las señaladas en la Ley 964 de 2005, la Ley 27 de 1990, el Decreto 2555 de 2010, el Decreto 3960 de 2010 y las normas que en futuro las modifiquen o adicionen
             </td>
             <td style="width: 25%; border: 1px solid #B7BAB7; font-size:10px; text-align: center">pagina 5 de 8</td>
             </tr>

            </table>
EOD;
            TCPDF::writeHTML($tbl, true, false, false, false, '');

            TCPDF::SetFont('helvetica',"" ,11);
            $tbl = '<p style="text-align: justify;">
            prohibido a <span style="font-weight: bold">EL MANDANTE</span> permitir que sus modelos accedan a las cuentas, compartan información de inicio de sesión, contraseñas, información o datos de cuentas con otras personas no autorizadas previamente por <span style="font-weight: bold">EL MANDATARIO</span>.
            <br>
            N. <span style="font-weight: bold">EL MANDANTE</span> está obligado a tratar de forma privada y confidencial los datos personales de sus empleados, contratistas, asociados y clientes, así como del personal de <span style="font-weight: bold">EL MANDATARIO</span>, limitando el acceso a la información por parte de terceros teniendo cuidado de revelar la información de manera no autorizada.
            <br>
            <span style="font-weight: bold">PARAGRAFO. EL MANDATARIO</span> nunca será responsable del pago de prestaciones sociales a <span style="font-weight: bold">EL MANDANTE</span>, ni al personal contratado por el y no existe ni existirá solidaridad entre <span style="font-weight: bold">EL MANDATARIO</span> y <span style="font-weight: bold">EL MANDANTE</span> en relación con los empleados o contratistas de una parte frente a la otra.
            <br><br>
            <span style="font-weight: bold">SEXTA:  CONTROL  AL  LAVADO DE ACTIVOS: EL MANDANTE</span> autoriza a <span style="font-weight: bold">EL MANDATARIO</span> a intercambiar información  con el Banco de Ia Republica o la entidad financiera que lo requiera en temas de Prevención y Control del Lavado de Activos y autoriza a  <span style="font-weight: bold">EL MANDATARIO</span> para solicitar cancelar las cuentas a subcuentas que estén abiertas a su nombre, cuando quiera que se encuentre vinculado de alguna manera a listas  de  publica  circulación  internacionales o locales  relacionadas  con  delitos tipificados en Colombia como lavado de activos, así en Colombia no se hubiere iniciado investigación sobre el particular.
            <br><br>
            <span style="font-weight: bold">SEPTIMA - CONFIDENCIALIDAD:</span> Reconoce <span style="font-weight: bold">EL MANDANTE</span>  que toda Ia información señalada  en el presente documento y toda Ia documentación e información que <span style="font-weight: bold">EL MANDATARIO</span> suministre es de propiedad de <span style="font-weight: bold">EL MANDATARIO</span> y Ia misma será  considerada como  INFORMACION  CONFIDENCIAL por lo tanto  <span style="font-weight: bold">EL MANDANTE</span> se obliga a guardarla en secreto, por lo que no podrá hacerla del conocimiento de persona alguna, no divulgara la información que en forma personal maneje, ni Ia proporcionara a terceras personas en forma verbal o escrita, por medios electrónicos, magnéticos, o por cualquier otro medio directa o indirectamente, por el contrario se obliga a conservarla y no divulgarla.
            <br><br>
            <span style="font-weight: bold">OCTAVA - SDN LIST. EL MANDANTE</span> declara, bajo Ia gravedad del juramento, que no esta actualmente, ni ha estado jamás, incluido en Ia lista Special Designated Nationals and Blocked Persons, emitida por at Departamento del Tesoro de los Estados Unidos de América (esta lista es conocida como la Lista Clinton). también declara que no es socio, accionista, inversionista o participante en sociedades a empresas, de cualquier nacionalidad, que estén actualmente, o hayan estado jamás, incluidas en Ia mencionada lista. Si se demuestra que esta declaración jurada de <span style="font-weight: bold">EL MANDANTE</span> es falsa, este contrato terminará   automáticamente y será un incumplimiento gravísimo de las obligaciones a cargo de <span style="font-weight: bold">EL MANDANTE</span>. Como indemnización, en este caso, <span style="font-weight: bold">EL MANDANTE</span> deberá pagar a <span style="font-weight: bold">EL MANDATARIO</span> todas las sumas que correspondan a lucro cesante y daño emergente según la estimación de perjuicios que para el efecto realice <span style="font-weight: bold">EL MANDATARIO</span> en el momento de Ia terminación del contrato por la cause a la que se refiere esta cláusula; <span style="font-weight: bold">EL MANDANTE</span> renuncia por esta media a oponerse a Ia estimación de perjuicios que en estos términos realice <span style="font-weight: bold">EL MANDATARIO</span>.

            </p>';
            TCPDF::writeHTML($tbl, true, false, false, false, '');


            TCPDF::SetFont('helvetica',"" ,10);
            TCPDF::SetXY(40, 270);
            $tbl = <<<EOD
            <span>
                DIRECCIÓN: CL 5 No. 39 – 119 Barrio Tequendama / CALI – COLOMBIA</span>
EOD;
            TCPDF::writeHTML($tbl, true, false, false, false, '');

            TCPDF::SetFont('helvetica',"" ,9);
            TCPDF::SetXY(40, 275);
            $tbl = <<<EOD
            <span>
              Tel. (+57) 0323865120 Cel. 3009109951 – (+57) 3227487143 (solo chat)</span>
EOD;
            TCPDF::writeHTML($tbl, true, false, false, false, '');

            TCPDF::SetFont('helvetica',"" ,9);
            TCPDF::SetXY(40, 280);
            $tbl = <<<EOD
            <span>
              Página web: www.grupo-bedoya.com / email: info@grupo-bedoya.com</span>
EOD;
            TCPDF::writeHTML($tbl, true, false, false, false, '');

            //6ta pagina
            TCPDF::AddPage();
            TCPDF::setJPEGQuality(75);
            $tbl = <<<EOD
            <table style="border: 1px solid #B7BAB7; text-align: center; padding: 5px 0"  nobr="true">
             <tr>
              <td><img src="images/default/logoiva.jpg"  width="280" height="50"></td>
              <td style="border: 1px solid #B7BAB7; font-size:10px; text-align: center">CONTRATO DE MANDATO <br>
            De acuerdo por las disposiciones del Código de Comercio y del Código Civil, en especial las señaladas en la Ley 964 de 2005, la Ley 27 de 1990, el Decreto 2555 de 2010, el Decreto 3960 de 2010 y las normas que en futuro las modifiquen o adicionen
             </td>
             <td style="width: 25%; border: 1px solid #B7BAB7; font-size:10px; text-align: center">pagina 6 de 8</td>
             </tr>

            </table>
EOD;
            TCPDF::writeHTML($tbl, true, false, false, false, '');

            TCPDF::SetFont('helvetica',"" ,11);
            $tbl = '<p style="text-align: justify;">
            <span style="font-weight: bold">NOVENA: INDEMNIDAD.</span> - Las partes se obligan a mantenerse indemnes mutuamente de cualquier reclamación de cualquier naturaleza proveniente de terceros, derivada del incumplimiento de sus obligaciones contractuales.  En caso de presentarse tales reclamaciones, la parte incumplida será Ia única responsable de indemnizar los diarios causados a terceros por su negligencia.
            <br><br>
            <span style="font-weight: bold">DECIMA: NULIDAD PARCIAL.</span> Si cualquier articulo y/o cláusula de este contrato es declarado invalido por juez competente, o por un tribunal de arbitramento no se afectará la validez y vigencia del resto de los artículos y/o clausulas.
            <br><br>
            <span style="font-weight: bold">DECIMA PRIMERA: CLAUSULA COMPROMISORIA.</span> Si surgiere alguna diferencia, disputa o controversia entre las partes por razón con ocasión del presente contrato, las partes buscaran de buena fe un arreglo directo antes de acudir al trámite arbitral aquí previsto. En consecuencia, si surgiere alguna diferencia, cualquiera de las partes notificará a la otra la existencia de dicha diferencia y una etapa de arreglo directo surgirá desde el día siguiente a la respectiva notificación. Esta etapa de arreglo directo culminara a los treinta (30) días siguientes a la fecha de su comienzo.
            Si no hubiere arreglo entre las partes dentro de la etapa antedicha, cualquiera de ellas podrá dar inicio al arbitraje institucional. En consecuencia, la diferencia, disputa o controversia correspondiente será sometida a Ia decisión definitiva y vinculante de un Tribunal de Arbitramento así: Cualquier controversia o reclamación que surja de o relativa al presente contrato, será resuelta por arbitraje obligatorio para las partes, y serán sometidas a la decisión de un Tribunal de Arbitramento designado para la Cámara de Comercio de Cali, mediante sorteo entre los árbitros inscritos en las listas que lleva dicha Cámara. El tribunal constituido se sujetará a lo dispuesto por Ia Ley 446 de 1.998, el Decreto 2279 de 1989, modificado por la Ley 23 de 1991, Ia Ley 640 de 2001 y a las demás disposiciones legales que lo modifiquen o adicionen, de acuerdo con las siguientes reglas:
            <br>
            a) El Tribunal estará integrado por un (1) arbitro.
            <br>
            b) La organización interna del Tribunal se sujetará a las reglas previstas para el afecta, para el Centro de Arbitraje y Conciliación Comercial de la Cámara de Comercio de Cali.
            <br>
            c) El Tribunal decidirá en derecho y el Tribunal funcionará en esta Ciudad de Cali en el Centro de Arbitraje y Conciliación de Ia Cámara de Comercio de Cali.
            <br><br>
            <span style="font-weight: bold">DECIMA SEGUNDA – CESIÓN DE DERECHOS: EL MANDANTE</span> manifiesta de manera irrevocable que está autorizado para otorgarle los derechos de imagen de sus modelos a <span style="font-weight: bold">EL MANDATARIO</span> y por tanto este queda facultado para utilizar sus nombres artísticos, imágenes, actuaciones, audios, fotos, vídeos, chat, documentos y cualquier otro aspecto asociado a la interacción de páginas web para adultos, a nivel internacional, por lo cual concede y asigna a <span style="font-weight: bold">EL MANDATARIO</span> todos los derechos, títulos, intereses y derechos de autor asociados con sus modelos, es decir que también pasan a ser propiedad de <span style="font-weight: bold">EL MANDATARIO</span>.
            <br><br>
            <span style="font-weight: bold">PARÁGRAFO PRIMERO - CESIÓN DEL CONTRATO: EL MANDANTE</span> no podrá ceder el presente contrato parcial o totalmente sin el previo consentimiento escrito de <span style="font-weight: bold">EL MANDATARIO</span>.


            </p>';
            TCPDF::writeHTML($tbl, true, false, false, false, '');


            TCPDF::SetFont('helvetica',"" ,10);
            TCPDF::SetXY(40, 270);
            $tbl = <<<EOD
            <span>
                DIRECCIÓN: CL 5 No. 39 – 119 Barrio Tequendama / CALI – COLOMBIA</span>
EOD;
            TCPDF::writeHTML($tbl, true, false, false, false, '');

            TCPDF::SetFont('helvetica',"" ,9);
            TCPDF::SetXY(40, 275);
            $tbl = <<<EOD
            <span>
              Tel. (+57) 0323865120 Cel. 3009109951 – (+57) 3227487143 (solo chat)</span>
EOD;
            TCPDF::writeHTML($tbl, true, false, false, false, '');

            TCPDF::SetFont('helvetica',"" ,9);
            TCPDF::SetXY(40, 280);
            $tbl = <<<EOD
            <span>
              Página web: www.grupo-bedoya.com / email: info@grupo-bedoya.com</span>
EOD;
            TCPDF::writeHTML($tbl, true, false, false, false, '');

            //7ta pagina
            TCPDF::AddPage();
            TCPDF::setJPEGQuality(75);
            $tbl = <<<EOD
            <table style="border: 1px solid #B7BAB7; text-align: center; padding: 5px 0"  nobr="true">
             <tr>
              <td><img src="images/default/logoiva.jpg"  width="280" height="50"></td>
              <td style="border: 1px solid #B7BAB7; font-size:10px; text-align: center">CONTRATO DE MANDATO <br>
            De acuerdo por las disposiciones del Código de Comercio y del Código Civil, en especial las señaladas en la Ley 964 de 2005, la Ley 27 de 1990, el Decreto 2555 de 2010, el Decreto 3960 de 2010 y las normas que en futuro las modifiquen o adicionen
             </td>
             <td style="width: 25%; border: 1px solid #B7BAB7; font-size:10px; text-align: center">pagina 7 de 8</td>
             </tr>

            </table>
EOD;
            TCPDF::writeHTML($tbl, true, false, false, false, '');

            TCPDF::SetFont('helvetica',"" ,11);
            $tbl = '<p style="text-align: justify;">
            <span style="font-weight: bold">DECIMA TERCERA:  CLAUSULA PENAL:</span>  En el evento de incumplimiento de cualquiera de las partes a las obligaciones a su cargo contenidas en la Ley a en este contrato, Ia parte incumplida deberá pagar a Ia otra parte, la suma de CINCUENTA MILLONES DE PESOS ($50.000.000).
            <br><br>
            <span style="font-weight: bold">DECIMA CUARTA: DIRECCIONES DE NOTIFICACION.</span> Para todos los efectos de este contrato, tanto <span style="font-weight: bold">EL MANDANTE</span> como <span style="font-weight: bold">EL MANDATARIO</span> declarán como direcciones para efectos comerciales y de notificación judicial los consignados en el encabezado del presente contrato.
            <br><br>
            Para constancia de lo anterior el presente contrato es suscrito en Cali - Valle, en dos ejemplares del mismo valor y tenor con destino a cada una de las partes el día <span style="font-weight: bold">('.$dia.')</span> del mes de <span style="font-weight: bold">'.$meses[$mes].'</span> del año <span style="font-weight: bold">('.$year.')</span>, reconociendo en su totalidad el contenido del mismo.


            </p>';
            TCPDF::writeHTML($tbl, true, false, false, false, '');
            if ($contract->company_type == "Persona Natural") {
                $actuando = "";
            }
            else
            {
                $actuando = 'Representante legal principal <br>'.$contract->company.' <br>NIT '.$contract->nit;
            }
            $tbl ='
            <br>
            <br>
            <br>
            <table  nobr="true">
             <tr>
             <td><span style="font-weight: bold">EL MANDANTE</span></td>
              <td><img src="images/default/square.png"  width="150" height="150"></td>
             </tr>
             <tr>
             <td>
            ___________________________________
             <br>
             <br>
            <span style="font-weight: bold">'.$contract->holder.'</span>
            <br>
            <span style="font-weight: bold">CC '.$contract->card_id.'</span>
            <br>
            <span style="font-weight: bold">'.$actuando.'</span>
             </td>
             <td>HUELLA ÍNDICE DERECHO</td>
             </tr>

            </table>';
            TCPDF::writeHTML($tbl, true, false, false, false, '');


            $tbl ='
            <br>
            <br>
            <br>
            <table  nobr="true">
             <tr>
             <td><span style="font-weight: bold">EL MANDATARIO</span></td>
             <td></td>
             </tr>
             <tr>
             <td>
            <br>
            <br>
            <br>
            <br>
            <br>
            ___________________________________
             <br>
             <br><span style="font-weight: bold">RICHARD ANDRÉS BEDOYA JOSEPH
            <br>
            C.C. 94.474.531
            <br>
            Representante legal principal
            <br>
            GB MEDIA GROUP S.A.S.
            <br>
            NIT. 901.145.956-7
            </span>
             </td>
             <td></td>
             </tr>

            </table>';
            TCPDF::writeHTML($tbl, true, false, false, false, '');

            TCPDF::SetFont('helvetica',"" ,10);
            TCPDF::SetXY(40, 270);
            $tbl = <<<EOD
            <span>
                DIRECCIÓN: CL 5 No. 39 – 119 Barrio Tequendama / CALI – COLOMBIA</span>
EOD;
            TCPDF::writeHTML($tbl, true, false, false, false, '');

            TCPDF::SetFont('helvetica',"" ,9);
            TCPDF::SetXY(40, 275);
            $tbl = <<<EOD
            <span>
              Tel. (+57) 0323865120 Cel. 3009109951 – (+57) 3227487143 (solo chat)</span>
EOD;
            TCPDF::writeHTML($tbl, true, false, false, false, '');

            TCPDF::SetFont('helvetica',"" ,9);
            TCPDF::SetXY(40, 280);
            $tbl = <<<EOD
            <span>
              Página web: www.grupo-bedoya.com / email: info@grupo-bedoya.com</span>
EOD;
            TCPDF::writeHTML($tbl, true, false, false, false, '');

            //8va pagina
            TCPDF::AddPage();
            TCPDF::setJPEGQuality(75);
            $tbl = <<<EOD
            <table style="border: 1px solid #B7BAB7; text-align: center; padding: 5px 0"  nobr="true">
             <tr>
              <td><img src="images/default/logoiva.jpg"  width="280" height="50"></td>
              <td style="border: 1px solid #B7BAB7; font-size:10px; text-align: center">CONTRATO DE MANDATO <br>
            De acuerdo por las disposiciones del Código de Comercio y del Código Civil, en especial las señaladas en la Ley 964 de 2005, la Ley 27 de 1990, el Decreto 2555 de 2010, el Decreto 3960 de 2010 y las normas que en futuro las modifiquen o adicionen
             </td>
             <td style="width: 25%; border: 1px solid #B7BAB7; font-size:10px; text-align: center">pagina 8 de 8</td>
             </tr>

            </table>
EOD;
            TCPDF::writeHTML($tbl, true, false, false, false, '');

            TCPDF::SetFont('helvetica',"" ,11);
            $tbl = '<p style="text-align: justify;">
            En cumplimiento de la normatividad vigente, <span style="font-weight: bold">GB MEDIA GROUP S.A.S.</span>, sociedad con domicilio en la ciudad de CALI – COLOMBIA, identificada con <span style="font-weight: bold">NIT. 901.145.956-7</span>, representada legalmente por <span style="font-weight: bold">Richard Andrés Bedoya Joseph</span>, identificado con cédula de ciudadanía <span style="font-weight: bold">No. 94.474.531</span>, requiere de su autorización para recolectar, almacenar, usar, circular y suprimir su información personal con la finalidad asociada al cumplimiento de las obligaciones que se derivan del proceso de registro ante terceros usando plataformas tecnológicas, y especialmente para identificarlo (a) ante los procesos dentro de la empresa. La información que por parte de nuestra compañía se recolecta no se utilizará de manera práctica, misional y/o funcional para enviarle información sobre novedades, noticias y promociones propias y de terceros, en forma directa o a través de terceros a menos que nos autorice a ello. Si usted autoriza a nuestra compañía para enviarle información sobre novedades, noticias y promociones propias y de terceros, en forma directa o a través de terceros, por favor diligenciar marcando con X en la casilla correspondiente: ________________';
            TCPDF::writeHTML($tbl, true, false, false, false, '');
            TCPDF::SetFont('helvetica',"" ,10);
            $tbl = '<br>
            <table style="border: 1px solid #B7BAB7; padding: 3px 5px"  nobr="true">
            <tr>
            <td style="border: 1px solid #B7BAB7;">AUTORIZO</td>
            <td style="border: 1px solid #B7BAB7;">Si:__X__</td>
            <td style="border: 1px solid #B7BAB7;">No:_____</td>
            </tr>
            </table>';
            TCPDF::writeHTML($tbl, true, false, false, false, '');
            TCPDF::SetFont('helvetica',"" ,11);
            $tbl = 'Nuestra compañía en cualquier momento podrá comunicar a terceros dentro y fuera del país, sus datos personales no sensibles, esta actividad solo tendrá lugar en situaciones inherentes o con relación al cumplimiento de los contratos ya firmados o que en lo sucesivo se llegaren a firmar entre usted y nosotros. El titular de los datos podrá ejercitar los derechos de acceso, corrección, supresión, revocación o reclamo por infracción mediante un escrito dirigido a la compañía a la dirección de correo electrónico que para ello esté dispuesto, indicando en el asunto “modificación o corrección de datos personales”, o mediante comunicación escrita y entregada en las oficinas de nuestra compañía. La política de tratamiento de datos a la que se encuentran sujetos los datos personales se podrá consultar en la página web de la compañía. La información de carácter personal asociada a este documento será conservada por cinco (5) años que contaremos a partir de su vinculación.';
            TCPDF::writeHTML($tbl, true, false, false, false, '');
            TCPDF::SetFont('helvetica',"" ,10);
            $tbl = '<br>
            <br>
            <table style="border: 1px solid #0A0A0A; padding: 3px 5px"  nobr="true">
            <tr>
            <td  colspan="4" style="border: 1px solid #0A0A0A; background-color: #B0B0B0;">Autorizo el tratamiento de mi huella digital.</td>
            <td style="border: 1px solid #0A0A0A;">Si:__X__</td>
            <td style="border: 1px solid #0A0A0A;">No:_____</td>
            </tr>
            <tr>
            <td  colspan="4" style="border: 1px solid #0A0A0A; background-color: #B0B0B0;">Autorizo el tratamiento de mi(s) fotografía(s) para los registros necesarios.</td>
            <td style="border: 1px solid #0A0A0A;">Si:__X__</td>
            <td style="border: 1px solid #0A0A0A;">No:_____</td>
            </tr>
            <tr>
            <td  colspan="4" style="border: 1px solid #0A0A0A; background-color: #B0B0B0;">Autorizo el envío de información requerida para registros</td>
            <td style="border: 1px solid #0A0A0A;">Si:__X__</td>
            <td style="border: 1px solid #0A0A0A;">No:_____</td>
            </tr>
            <tr>
            <td  colspan="4" style="border: 1px solid #0A0A0A; background-color: #B0B0B0;">Autorizo la digitalización de mi documento de identificación.</td>
            <td style="border: 1px solid #0A0A0A;">Si:__X__</td>
            <td style="border: 1px solid #0A0A0A;">No:_____</td>
            </tr>
            <tr>
            <td  colspan="4" style="border: 1px solid #0A0A0A; background-color: #B0B0B0;">Autorizo el tratamiento de mi nombre personal.</td>
            <td style="border: 1px solid #0A0A0A;">Si:__X__</td>
            <td style="border: 1px solid #0A0A0A;">No:_____</td>
            </tr>
            <tr>
            <td  colspan="4" style="border: 1px solid #0A0A0A; background-color: #B0B0B0;">Autorizo el tratamiento de mi nombre artístico</td>
            <td style="border: 1px solid #0A0A0A;">Si:__X__</td>
            <td style="border: 1px solid #0A0A0A;">No:_____</td>
            </tr>
            <tr>
            <td  colspan="4" style="border: 1px solid #0A0A0A; background-color: #B0B0B0;">Autorizo el tratamiento de mi(s) video(s) para los registros necesarios.</td>
            <td style="border: 1px solid #0A0A0A;">Si:__X__</td>
            <td style="border: 1px solid #0A0A0A;">No:_____</td>
            </tr>
            <tr>
            <td  colspan="4" style="border: 1px solid #0A0A0A; background-color: #B0B0B0;">Autorizo el tratamiento de mi(s) grabación(es) para los registros necesarios.</td>
            <td style="border: 1px solid #0A0A0A;">Si:__X__</td>
            <td style="border: 1px solid #0A0A0A;">No:_____</td>
            </tr>
            </table>
            </p>';
            TCPDF::writeHTML($tbl, true, false, false, false, '');

            TCPDF::SetFont('helvetica',"",8);
            $tbl = '
            <br>
            <table style="border: 1px solid #0A0A0A; padding: 3px 5px"  nobr="true">
            <tr>
            <td style="border: 1px solid #0A0A0A; background-color: #B0B0B0;">Fecha de la Firma</td>
            <td style="border: 1px solid #0A0A0A; background-color: #B0B0B0;" colspan="3">'.$dia.'/'.$mes.'/'.$year.' (Día/Mes/Año)</td>

            </tr>
            <tr>
            <td style="border: 1px solid #0A0A0A; background-color: #B0B0B0;">Firma</td>
            <td style="border: 1px solid #0A0A0A;" colspan="3"></td>
            </tr>
            <tr>
            <td style="border: 1px solid #0A0A0A; background-color: #B0B0B0;">Nombres Completos <br>Empresa:</td>
            <td style="border: 1px solid #0A0A0A;" colspan="2">'.$contract->holder.' <br>'.$contract->company.'/ NIT '.$contract->nit.'</td>
            <td style="border: 1px solid #0A0A0A;" rowspan="2"></td>
            </tr>
            <tr>
            <td style="border: 1px solid #0A0A0A; background-color: #B0B0B0;">Correo Electónico</td>
            <td style="border: 1px solid #0A0A0A;" colspan="2">'.$contract->email.'</td>
            <td style="border: 1px solid #0A0A0A;"></td>
            </tr>
            <tr>
            <td style="border: 1px solid #0A0A0A; background-color: #B0B0B0;">Dirección <br>Teléfonos</td>
            <td style="border: 1px solid #0A0A0A;" colspan="2">'.$contract->address.' '.$contract->city.' '.$contract->department.' <br>'.$contract->phone.'</td>
            <td style="background-color: #B0B0B0; text-align: center">Huella</td>
            </tr>
            </table>';
            TCPDF::writeHTML($tbl, true, false, false, false, '');

            TCPDF::SetFont('helvetica',"" ,8);
            TCPDF::SetXY(40, 270);
            $tbl = <<<EOD
            <span>
                DIRECCIÓN: CL 5 No. 39 – 119 Barrio Tequendama / CALI – COLOMBIA</span>
EOD;
            TCPDF::writeHTML($tbl, true, false, false, false, '');

            TCPDF::SetFont('helvetica',"" ,9);
            TCPDF::SetXY(40, 275);
            $tbl = <<<EOD
            <span>
              Tel. (+57) 0323865120 Cel. 3009109951 – (+57) 3227487143 (solo chat)</span>
EOD;
            TCPDF::writeHTML($tbl, true, false, false, false, '');

            TCPDF::SetFont('helvetica',"" ,9);
            TCPDF::SetXY(40, 280);
            $tbl = <<<EOD
            <span>
              Página web: www.grupo-bedoya.com / email: info@grupo-bedoya.com</span>
EOD;
            TCPDF::writeHTML($tbl, true, false, false, false, '');

            TCPDF::Output('Invoice', 'I');
        });

    }

    public function removeCommission(Request $request)
    {
        $commission = SatelliteOwnerCommissionRelation::find($request->id);
        $commission->delete();
        return true;
    }

    public function removeUser(Request $request)
    {
        SatelliteUsersImage::where('satellite_user_id', $request->user_id)->delete();
        $user = SatelliteUser::find($request->user_id);
        $user->delete();
        return true;
    }

    public function storeAccount(Request $request)
    {
        try {
            DB::beginTransaction();

            $this->validate($request,
                [
                    'nick' => ['required', Rule::unique('satellite_accounts')->where(function ($query) use ($request) {
                        return $query->where('nick', $request->nick)->where('page_id', $request->page);
                    })],
                    'owner_placeholder' => 'required',
                    'page' => 'required',
                ],
                [
                    'nick.required' => 'Este campo es obligatorio',
                    'nick.unique' => 'Esta cuenta ya existe',
                    'owner_placeholder.required' => 'Este campo es obligatorio',
                    'page.required' => 'Este campo es obligatorio',
                ]);

            $page_field = SatelliteTemplatesPagesField::where('id', $request->page)->where('template_type_id', 1)->first();

            $account = new SatelliteAccount;
            $account->owner_id = $request->owner_id;
            $account->page_id = $request->page;
            $account->status_id = 1;
            $account->nick = trim($request->nick);
            $account->original_nick = trim($request->nick);
            $account->first_name = trim($request->first_name);
            $account->second_name = trim($request->second_name);
            $account->last_name = trim($request->last_name);
            $account->second_last_name = trim($request->second_last_name);
            $account->birth_date = $request->birth_date;
            $account->access = trim($request->access);
            $account->password = trim($request->password);
            $account->live_id = trim($request->live_id);
            $account->modified_by = Auth::user()->id;
            $account->from_gb = 0;

            $send_email = true;
            $email_send_status = 0;

            if ($page_field->full_name == 1 && ($request->first_name == "" || $request->last_name == "")) {
                $send_email = false;
                $email_send_status = 4;
            }

            if ($page_field->access == 1 && $request->access == "") {
                $send_email = false;
                $email_send_status = 4;
            }

            if ($page_field->password == 1 && $request->password == "") {
                $send_email = false;
                $email_send_status = 4;
            }

            if ($page_field->template_page->count() <= 0) {
                $send_email = false;
                $email_send_status = 3;
            }

            if
            (
                preg_match("/^\.+$/", $request->first_name) ||
                preg_match("/^\.+$/", $request->last_name) ||
                preg_match("/^\.+$/", $request->first_name) ||
                preg_match("/^\.+$/", $request->last_name)
            ) // Si el nombre u apellido tiene solo comas o puntos, no enviar el correo
            {
                $send_email = false;
                $email_send_status = 1;
            }

            if ($request->account_type == "with_user") {
                $account->satellite_user_id = $request->satellite_user_id;
            }

            $owner_email = '';

            if($send_email) {
                $owner = SatelliteOwner::select('email')->where('id', $request->owner_id)->first();
                if(is_null($owner)) { // No owner
                    $email_send_status = 2;
                } else {
                    $owner_email = $owner->email;

                    $full_name = $request->first_name." ".$request->second_name." ".$request->last_name." ".$request->second_last_name;
                    $mail['subject'] = $page_field->template_page[0]->subject;
                    $mail['pagina'] = $page_field->name;

                    $body = $page_field->template_page[0]->body;
                    $body = str_replace("{{email}}", $request->email, $body);
                    $body = str_replace("{{nick}}", $request->nick, $body);
                    $body = str_replace("{{full_name}}", $full_name, $body);
                    $body = str_replace("{{access}}", $request->access, $body);
                    $body = str_replace("{{password}}", $request->password, $body);
                    $mail['body'] = $body;
                    $sent = Mail::to($owner->email)->send(new CreatedAccount($mail));
                    //$sent = Mail::to("romangbmediagroup@gmail.com")->send(new CreatedAccount($mail));
                    //$sent = Mail::to("manuelgbmediagroup@gmail.com")->send(new CreatedAccount($mail));
                    $account->email_sent = $send_email;
                }
            }

            $account->save();

            if ($request->partner != "")
            {
                $partner_array = explode("," , $request->partner);

                for ($i=0; $i < count($partner_array) ; $i++) {
                    $partner = new SatelliteAccountPartner;
                    $partner->account_id = $account->id;
                    $partner->name = $partner_array[$i];
                    $partner->save();
                }
            }

            //verificando si la cuenta esta en Resumen de Archivos Subidos
            $file = SatellitePaymentFile::select('payment_date', 'trm')->orderBy('payment_date', 'desc')->first();
            $payment_accounts = SatellitePaymentAccount::where('owner_id', null)->where('page_id', $account->page_id)
                ->where('nick', $account->nick)->where('payment_date', $file->payment_date)->get();

            foreach ($payment_accounts as $key => $payment_account) {

                if ($account->status_id == 1 || $account->status_id == 6 || $account->status_id == 8) {
                    $account->status_id = 2;
                    $account->save();
                }

                $payment_account->owner_id = $account->owner_id;
                $payment_account->account_id = $account->id;
                $payment_account->save();
                $owner = SatelliteOwner::select('owner')->where('id', $account->owner_id)->get();

                $receivers = SatelliteOwnerCommissionRelation::select('owner_receiver')->distinct('owner_receiver')->where('owner_giver', $account->owner_id)->get();

                foreach ($receivers as $receiver) {
                    $this->createCommisionForReceiver($receiver->owner_receiver, $account->owner_id, $payment_account->payment_date);
                }

                $payment_account_alert = [
                    "id" => $payment_account->id,
                    "owner_id" => $payment_account->owner_id,
                    "owner_name" => $owner[0]->owner,
                    "account_id" => $payment_account->account_id,
                ];
                event(new PaymentAccount($payment_account_alert));
            }

            DB::commit();
            return response()->json(['success' => true, 'email_sent' => $send_email, 'email_send_status' => $email_send_status, 'owner_email' => $owner_email]);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json(['success' => false]);
        }
    }

    public function storeOwner(Request $request)
    {
        $this->folderExists('satellite/owner');
        $this->validate($request,
        [
            'owner' => 'required|unique:satellite_owners,owner',
            'email' => 'required|unique:satellite_owners,email',
            'document_number' => 'nullable|numeric',
            'phone' => 'nullable|numeric',
        ],
        [
            'owner.required' => 'Este campo es obligatorio',
            'owner.unique' => 'El propietario ya existe',
            'email.required' => 'Este campo es obligatorio',
            'email.unique' => 'Ya existe un propietario con ese email',
            'document_number.numeric' => 'Este campo debe ser numerico',
            'phone.numeric' => 'Este campo debe ser numerico',
        ]);

        try {
            DB::beginTransaction();

            $owner = new SatelliteOwner;
            $owner->owner = $request->owner;
            $owner->email = $request->email;
            $owner->first_name = $request->first_name;
            $owner->second_name = $request->second_name;
            $owner->last_name = $request->last_name;
            $owner->second_last_name = $request->second_last_name;
            $owner->document_number = $request->document_number;
            $owner->phone = $request->phone;
            $owner->others_emails = $request->others_emails;
            $owner->statistics_emails = $request->statistics_emails;
            $owner->department_id = $request->department;
            $owner->city_id = $request->city;
            $owner->address = $request->address;
            $owner->neighborhood = $request->neighborhood;
            $owner->save();

            if ($request->file('rut'))
            {
                $files = $request->file('rut');
                foreach($files as $file){
                    $owner_documentation = new SatelliteOwnerDocumentation;
                    $owner_documentation->owner = $owner->id;
                    $owner_documentation->type = 1;
                    $owner_documentation->file = $this->uploadFile($file, 'satellite/owner');
                    $owner_documentation->save();
                }
            }

            if ($request->file('chamber_commerce'))
            {
                $files = $request->file('chamber_commerce');
                foreach($files as $file){
                    $owner_documentation = new SatelliteOwnerDocumentation;
                    $owner_documentation->owner = $owner->id;
                    $owner_documentation->type = 2;
                    $owner_documentation->file = $this->uploadFile($file, 'satellite/owner');
                    $owner_documentation->save();
                }
            }

            if ($request->file('shareholder_structure'))
            {
                $files = $request->file('shareholder_structure');
                foreach($files as $file){
                    $owner_documentation = new SatelliteOwnerDocumentation;
                    $owner_documentation->owner = $owner->id;
                    $owner_documentation->type = 3;
                    $owner_documentation->file = $this->uploadFile($file, 'satellite/owner');
                    $owner_documentation->save();
                }
            }

            if ($request->file('bank_certification'))
            {
                $files = $request->file('bank_certification');
                foreach($files as $file){
                    $owner_documentation = new SatelliteOwnerDocumentation;
                    $owner_documentation->owner = $owner->id;
                    $owner_documentation->type = 4;
                    $owner_documentation->file = $this->uploadFile($file, 'satellite/owner');
                    $owner_documentation->save();
                }
            }

            $payment_info = new SatelliteOwnerPaymentInfo;
            $payment_info->owner = $owner->id;
            $payment_info->save();

            DB::commit();
            return response()->json(['success' => true, 'owner_id' => $owner->id]);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json(['success' => false]);
        }
    }

    public function storeProspect(Request $request)
    {
        $this->folderExists("satellite/prospect");
        $this->validate($request,
            [
                'first_name' => 'required',
                'last_name' => 'required',
                'studio' => 'required',
            ],
            [
                'first_name.required' => 'Este campo es obligatorio',
                'last_name.required' => 'Este campo es obligatorio',
                'studio.required' => 'Este campo es obligatorio',
            ]);

        try {
            DB::beginTransaction();

            $prospect = new SatelliteProspect;
            $prospect->owner = $request->owner;
            $prospect->first_name = $request->first_name;
            $prospect->second_name = $request->second_name;
            $prospect->last_name = $request->last_name;
            $prospect->second_last_name = $request->second_last_name;
            $prospect->document_number = $request->document_number;
            $prospect->email = $request->email;
            $prospect->phone = $request->phone;
            $prospect->address = $request->address;
            $prospect->neighborhood = $request->neighborhood;
            $prospect->department_id = $request->department;
            $prospect->city_id = $request->city;
            $prospect->studio = $request->studio;
            $prospect->note = $request->note;


            if ($request->file('rut'))
            {
                $file = $request->file('rut');
                $prospect->rut = $this->uploadFile($file, 'satellite/prospect');

            }

            $prospect->save();

            DB::commit();
            return response()->json(['success' => true, 'prospect' => $prospect]);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json(['success' => false]);
        }
    }

    public function getStudios()
    {
        $tenants = Tenant::all();
        return response()->json($tenants);
    }

    public function storeUser(Request $request)
    {

        $this->folderExists('satellite/user');
    	$this->validate($request,
        [
            'document_type' => 'required',
            'document_number' => 'required|numeric',
            'first_name' => 'required',
            'last_name' => 'required',
            'birth_date' => 'required',
            'front_image' => 'required|image',
            'back_image' => 'required|image',
        ],
        [
            'document_type.required' => 'Este campo es obligatorio',
            'document_number.required' => 'Este campo es obligatorio',
            'document_number.numeric' => 'Este campo debe ser numerico',
            'first_name.required' => 'Este campo es obligatorio',
            'last_name.required' => 'Este campo es obligatorio',
            'birth_date.required' => 'Este campo es obligatorio',
            'front_image.image' => 'Este campo es obligatorio',
            'back_image.image' => 'Este campo es obligatorio',
        ]);

        try {
        	DB::beginTransaction();

            $country_id = ($request->document_type <= 3)? 49 : $request->country_id;

            $result = SatelliteUser::where('document_type', $request->document_type)
            ->where('document_number', $request->document_number)
            ->where('country_id', $country_id)
            ->first();

            if ($result != null) {
                return response()->json(['success' => false]);
            }

            $user = new SatelliteUser;
            $user->first_name = $request->first_name;
            $user->second_name = $request->second_name;
            $user->last_name = $request->last_name;
            $user->second_last_name = $request->second_last_name;
            $user->document_type = $request->document_type;
            $user->document_number = $request->document_number;
            $user->country_id = $country_id;
            $user->birth_date = $request->birth_date;
            $user->created_by = Auth::user()->id;
            $user->modified_by = Auth::user()->id;
            $user->status = 1;
            $user->save();

            if ($request->file('front_image'))
            {
                $file = $request->file('front_image');
                $user_image = new SatelliteUsersImage;
                $user_image->satellite_user_id = $user->id;
                $user_image->type = 1;
                $user_image->image = $this->uploadFile($file, 'satellite/user');
                $user_image->save();
            }

            if ($request->file('back_image'))
            {
                $file = $request->file('back_image');
                $user_image = new SatelliteUsersImage;
                $user_image->satellite_user_id = $user->id;
                $user_image->type = 2;
                $user_image->image = $this->uploadFile($file, 'satellite/user');
                $user_image->save();
            }

            if ($request->file('holding_image'))
            {
                $file = $request->file('holding_image');
                $user_image = new SatelliteUsersImage;
                $user_image->satellite_user_id = $user->id;
                $user_image->type = 3;
                $user_image->image = $this->uploadFile($file, 'satellite/user');
                $user_image->save();
            }

            if ($request->file('profile_image'))
            {
                $file = $request->file('profile_image');
                $user_image = new SatelliteUsersImage;
                $user_image->satellite_user_id = $user->id;
                $user_image->type = 4;
                $user_image->image = $this->uploadFile($file, 'satellite/user');
                $user_image->save();
            }

        	DB::commit();
        	return response()->json(['success' => true]);
        } catch (Exception $e) {
        	DB::rollback();
        	return response()->json(['success' => false]);
        }
    }

    public function storeCommission(Request $request)
    {
        $this->validate($request,
        [
            'percent' => 'required|numeric',
        ],
        [
            'percent.required' => 'Este campo es obligatorio',
            'percent.numeric' => 'Este campo debe ser numerico',
        ]);

        $page = null;
        if ($request->type != 1)
        {
            $this->validate($request,
            [
                'page' => 'required',
            ],
            [
                'page.required' => 'Este campo es obligatorio para esta opcion',
            ]);

            $page = $request->page;
        }

        try {
            DB::beginTransaction();

            $commission = new SatelliteOwnerCommissionRelation;
            $commission->owner_giver = $request->owner_giver;
            $commission->owner_receiver = $request->owner_receiver;
            $commission->percent = $request->percent;
            $commission->type = $request->type;
            $commission->page = $page;
            $commission->save();

            DB::commit();
            return response()->json(['success' => true]);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json(['success' => false]);
        }
    }

    public function storeNote(Request $request)
    {
        $this->validate($request,
        [
            'note' => 'required',
        ],
        [
            'note.required' => 'Este campo es obligatorio',
        ]);

        try {
            DB::beginTransaction();

            $note = new SatelliteAccountNote;
            $note->account_id = $request->account_id;
            $note->note = $request->note;
            $note->created_by = Auth::user()->id;
            $note->save();

            DB::commit();
            return response()->json(['success' => true]);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json(['success' => false]);
        }
    }

    public function storeContract(Request $request)
    {
        $this->validate($request,
            [
                'studio_name' => 'required',
                'company_type' => 'required',
                'holder' => 'required',
                'card_id' => 'required|numeric',
                'address' => 'required',
                'city' => 'required',
                'department' => 'required',
                'phone' => 'required',
                'email' => 'required',
                'percent' => 'required|numeric',
                'payment_method' => 'required',
                'clause' => 'required',
                'years' => 'required|numeric',
            ],
            [
                'studio_name.required' => 'Este campo es obligatorio',
                'company_type.required' => 'Este campo es obligatorio',
                'holder.required' => 'Este campo es obligatorio',
                'card_id.required' => 'Este campo es obligatorio',
                'card_id.numeric' => 'Este campo debe ser numerico',
                'address.required' => 'Este campo es obligatorio',
                'city.required' => 'Este campo es obligatorio',
                'department.required' => 'Este campo es obligatorio',
                'phone.required' => 'Este campo es obligatorio',
                'email.required' => 'Este campo es obligatorio',
                'percent.required' => 'Este campo es obligatorio',
                'percent.numeric' => 'Este campo debe ser numerico',
                'payment_method.required' => 'Este campo es obligatorio',
                'clause.required' => 'Este campo es obligatorio',
                'years.required' => 'Este campo es obligatorio',
                'years.numeric' => 'Este campo debe ser numerico',
            ]);

        if ($request->company_type == "Empresa")
        {
            $this->validate($request, [
                'nit' => 'required',
                'company' => 'required',
            ],[
                'nit.required' => 'Este campo es obligatorio',
                'company.required' => 'Este campo es obligatorio',
            ]);
        }

        try {
            DB::beginTransaction();
            $original_tenant = tenant();
            $tenant = Tenant::find(1);
            $contract = $tenant->run(function () use ($tenant, $request, $original_tenant) {

                $contract = new SatelliteContract;
                $contract->studio_name = $request->studio_name;
                $contract->company_type = $request->company_type;
                $contract->holder = $request->holder;
                $contract->card_id = $request->card_id;
                if ($request->company_type == "Empresa")
                {
                    $contract->company = $request->company;
                    $contract->nit = $request->nit;
                }
                else
                {
                    $contract->company = "";
                    $contract->nit = "";
                }
                $contract->address = $request->address;
                $contract->city = $request->city;
                $contract->department = $request->department;
                $contract->phone = $request->phone;
                $contract->email = $request->email;
                $contract->percent = $request->percent;
                $contract->payment_method = $request->payment_method;
                $contract->clause = $request->clause;
                $contract->years = $request->years;
                $contract->from = $original_tenant->id;
                $contract->from_name = $original_tenant->studio_name;
                $contract->save();
                return $contract;
            });

            DB::commit();
            return response()->json(['success' => true, 'contract' => $contract]);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json(['success' => false]);
        }
    }

    public function updateContract(Request $request)
    {
        $this->validate($request,
            [
                'studio_name' => 'required',
                'company_type' => 'required',
                'holder' => 'required',
                'card_id' => 'required|numeric',
                'address' => 'required',
                'city' => 'required',
                'department' => 'required',
                'phone' => 'required',
                'email' => 'required',
                'percent' => 'required|numeric',
                'payment_method' => 'required',
                'clause' => 'required',
                'years' => 'required|numeric',
            ],
            [
                'studio_name.required' => 'Este campo es obligatorio',
                'company_type.required' => 'Este campo es obligatorio',
                'holder.required' => 'Este campo es obligatorio',
                'card_id.required' => 'Este campo es obligatorio',
                'card_id.numeric' => 'Este campo debe ser numerico',
                'address.required' => 'Este campo es obligatorio',
                'city.required' => 'Este campo es obligatorio',
                'department.required' => 'Este campo es obligatorio',
                'phone.required' => 'Este campo es obligatorio',
                'email.required' => 'Este campo es obligatorio',
                'percent.required' => 'Este campo es obligatorio',
                'percent.numeric' => 'Este campo debe ser numerico',
                'payment_method.required' => 'Este campo es obligatorio',
                'clause.required' => 'Este campo es obligatorio',
                'years.required' => 'Este campo es obligatorio',
                'years.numeric' => 'Este campo debe ser numerico',
            ]);

        if ($request->company_type == "Empresa")
        {
            $this->validate($request, [
                'nit' => 'required',
                'company' => 'required',
            ],[
                'nit.required' => 'Este campo es obligatorio',
                'company.required' => 'Este campo es obligatorio',
            ]);
        }

        try {
            DB::beginTransaction();

            $original_tenant = tenant();
            $tenant = Tenant::find(1);
            $contract = $tenant->run(function () use ($tenant, $request, $original_tenant) {
                $contract = SatelliteContract::find($request->contract_id);
                $contract->studio_name = $request->studio_name;
                $contract->company_type = $request->company_type;
                $contract->holder = $request->holder;
                $contract->card_id = $request->card_id;
                if ($request->company_type == "Empresa")
                {
                    $contract->company = $request->company;
                    $contract->nit = $request->nit;
                }
                else
                {
                    $contract->company = "";
                    $contract->nit = "";
                }
                $contract->address = $request->address;
                $contract->city = $request->city;
                $contract->department = $request->department;
                $contract->phone = $request->phone;
                $contract->email = $request->email;
                $contract->percent = $request->percent;
                $contract->payment_method = $request->payment_method;
                $contract->clause = $request->clause;
                $contract->years = $request->years;
                $contract->save();
            });
            DB::commit();
            return response()->json(['success' => true, 'contract' => $contract]);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json(['success' => false]);
        }
    }

    public function storeLimit(Request $request)
    {
        $this->validate($request,
        [
            'purchase_limit' => 'required',
        ],
        [
            'purchase_limit.required' => 'Este campo es obligatorio',
        ]);

        try {
            DB::beginTransaction();

            $owner = SatelliteOwner::find($request->id);
            $owner->purchase_limit = $request->purchase_limit;
            $owner->save();

            DB::commit();
            return response()->json(['success' => true]);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json(['success' => false]);
        }
    }

    public function searchOwner(Request $request)
    {
        $owners = SatelliteOwner::select('id','owner','email','others_emails')
        ->where('email', 'like' , '%' . $request->search_email. '%')
        ->orWhere('others_emails', 'like' , '%' . $request->search_email. '%')->get();

        $result = "<table class='table table-striped'><tr><th></th><th>Nombre</th><th>Email</th><th>Otros Emails</th></tr>";

        foreach ($owners as $owner) {
            $result .= "<tr>";
            $json = [
                'id' => $owner->id,
                'owner' => $owner->owner,
            ];
            $result .= "<td><input type='radio' name='radio_owner' onclick='OwnerSelected(".json_encode($json).")'></td>";
            $result .= "<td>".$owner->owner."</td>";
            $result .= "<td>".$owner->email."</td>";
            $result .= "<td>".$owner->others_emails."</td>";
            $result .= "</tr>";
        }
        return $result;
    }

    public function sendEmailCreatedAccount(Request $request)
    {
        $account = SatelliteAccount::find($request->account_id);
        $page_field = SatelliteTemplatesPagesField::find($account->page_id);

        $email_sent = 1;

        if ($page_field->full_name == 1 && ($account->first_name == "" || $account->last_name == "")) {
            $email_sent = 0;
        }
        if ($page_field->access == 1 && $account->access == "") {
            $email_sent = 0;
        }
        if ($page_field->password == 1 && $account->password == "") {
            $email_sent = 0;
        }
        if ($page_field->template_page->count() <= 0){
            $email_sent = 0;
        }


        if ($email_sent == 1)
        {
            $full_name = $account->first_name." ".$account->second_name." ".$account->last_name." ".$account->second_last_name;
            $mail['subject'] = $page_field->template_page[0]->subject;
            $mail['pagina'] = $page_field->name;
            $body = $page_field->template_page[0]->body;
            $body = str_replace("((nick))", $account->nick, $body);
            $body = str_replace("((full_name))", $full_name, $body);
            $body = str_replace("((access))", $account->access, $body);
            $body = str_replace("((password))", $account->password, $body);
            $mail['body'] = $body;
            Mail::to("romangbmediagroup@gmail.com")->send(new CreatedAccount($mail));

            return response()->json([ "success" => true ]);
        }
        else
        {
            return response()->json([ "success" => false ]);
        }
    }

    /*public function sendStatisticEmails(Request $request)
    {
        //return response()->json(['success' => false]);
        try {
            $payrolls = SatellitePaymentPayroll::where('payment_date', $request->payment_date)->where('is_user', $request->is_user)
                ->orderBy('payment_methods_id', 'asc')->get();

            $mail = SatelliteTemplateStatistic::first();
            foreach ($payrolls as $payroll)
            {
                $payroll_send['payroll'][0]["payment_date"] = $payroll->payment_date;
                $payroll_send['payroll'][0]["payment_range"] = $payroll->payment_range;
                $payroll_send['payroll'][0]["total"] = $payroll->total;
                $payroll_send['payroll'][0]["percent_gb"] = $payroll->percent_gb;
                $payroll_send['payroll'][0]["percent_studio"] = $payroll->percent_studio;
                $payroll_send['payroll'][0]["trm"] = $payroll->trm;
                $payroll_send['payroll'][0]["transaction"] = $payroll->transaction;
                $payroll_send['payroll'][0]["retention"] = $payroll->retention;
                $payroll_send['payroll'][0]["payment"] = $payroll->payment;

                Mail::to("romangbmediagroup@gmail.com")->send(new OwnerStatistic($mail, $payroll->id ,$payroll_send, $payroll->owner_id, $request->payment_date));
            }

            return response()->json(['success' => true]);
        }
        catch (Exception $e) {
            return response()->json(['success' => false]);
        }
    }*/

    public function sendStatisticEmails(Request $request)
    {
        $payrolls = SatellitePaymentPayroll::where('payment_date', $request->payment_date)->where('is_user', $request->is_user)
            ->orderBy('payment_methods_id', 'asc')->get();

        foreach ($payrolls as $payroll)
        {
            $owner = SatelliteOwner::find($payroll->owner_id);
            if($owner == null){
                continue;
            }else{
                $owner_email = $owner->email;
                $owner_stats_emails = $owner->statistics_emails;
                $emails = explode(',', $owner_stats_emails);
                array_push($emails, $owner_email);

                $accounts_send = [];
                $payroll_accounts = SatellitePaymentAccount::where('payroll_id', $payroll->id)->get();

                foreach ($payroll_accounts as $key => $payroll_account)
                {
                    $accounts_send[$key]["payment_date"] = $payroll_account->payment_date;
                    $accounts_send[$key]["page"] = $payroll_account->page->name;
                    $accounts_send[$key]["nick"] = $payroll_account->nick;
                    $accounts_send[$key]["amount"] = $payroll_account->amount;
                    $accounts_send[$key]["description"] = $payroll_account->description;
                }

                $commission_send = [];
                $commissions = SatellitePaymentCommission::where('payroll_id', $payroll->id)->get();
                foreach ($commissions as $key => $commission)
                {
                    $commission_send[$key]["amount"] = $commission->amount;
                    $commission_send[$key]["assign_to"] = ($commission->assign_to == 2)? "Pesos" : "Dolares";
                    $commission_send[$key]["description"] = $commission->description;
                }

                $deduction_send = [];
                $deductions = SatellitePaymentDeduction::where([
                    ['owner_id', $payroll->owner_id],
                    ['payment_date', null]
                ])->orWhere([
                    ['owner_id', $payroll->owner_id],
                    ['payment_date', '<=' , $request->payment_date],
                    ['finished_date', '>=' , $request->payment_date],
                    ['status', 1],
                ])->orWhere([
                    ['owner_id', $payroll->owner_id],
                    ['payment_date', '<=' , $request->payment_date],
                    ['finished_date', null ],
                    ['status', 0],
                ])->get();
                foreach ($deductions as $key => $deduction)
                {
                    $deduction_send[$key]["created_at"] = date_format(date_create($deduction->created_at),"d M Y");
                    $deduction_send[$key]["total"] = $deduction->total;
                    $deduction_send[$key]["times_paid"] = $deduction->times_paid;
                    $deduction_send[$key]["deduction_to"] = ($deduction->deduction_to == 2)? "Pesos" : "Dolares";
                    $deduction_send[$key]["amount"] = $deduction->amount;
                    $deduction_send[$key]["description"] = $deduction->description;
                    $deduction_send[$key]["paydeduction"] = "";
                    $paydeductions = SatellitePaymentPayDeduction::where('deduction_id', $deduction->id)->get();
                    foreach ($paydeductions as $paydeduction)
                    {
                        if ($deduction_send[$key]["paydeduction"] == "")
                            $deduction_send[$key]["paydeduction"] = date_format(date_create($paydeduction->payment_date),"d M Y")." ($".$paydeduction->amount.")";
                        else
                            $deduction_send[$key]["paydeduction"] = ", ".date_format(date_create($paydeduction->payment_date),"d M Y")." ($".$paydeduction->amount.")";
                    }
                }

                $payroll_send['payroll'][0]["payment_date"] = $payroll->payment_date;
                $payroll_send['payroll'][0]["payment_range"] = $payroll->payment_range;
                $payroll_send['payroll'][0]["total"] = $payroll->total;
                $payroll_send['payroll'][0]["percent_gb"] = $payroll->percent_gb;
                $payroll_send['payroll'][0]["percent_studio"] = $payroll->percent_studio;
                $payroll_send['payroll'][0]["trm"] = $payroll->trm;
                $payroll_send['payroll'][0]["transaction"] = $payroll->transaction;
                $payroll_send['payroll'][0]["retention"] = $payroll->retention;
                $payroll_send['payroll'][0]["payment"] = $payroll->payment;

                $excel['payroll'] = $payroll_send;
                $excel['accounts'] = $accounts_send;
                $excel['commissions'] = $commission_send;
                $excel['deductions'] = $deduction_send;

                $path = "statistics/".$payroll->owner_id;
                if (!file_exists($path)) {
                    Storage::disk('local')->makeDirectory($path);
                }
                Excel::store(new PayrollStatistic($excel), $path.'/Pago.xlsx');

                foreach ($emails as $email){
                    if (!empty($email)){
                        Mail::to(trim($email))->send(new OwnerStatistics2($payroll->owner_id));
                        //Mail::to('romangbmediagroup@gmail.com')->send(new OwnerStatistics2($payroll->owner_id));
                    }
                }
                //Mail::to('romangbmediagroup@gmail.com')->send(new OwnerStatistics2($request->owner_id));

                $payroll->update([
                    'mail_send' => 1
                ]);

                $unlink_path = "app/".$path."/Pago.xlsx";
                unlink(storage_path($unlink_path));
            }//end else
        }

        return response()->json(['success' => true]);
    }

    public function sendStatisticEmail(Request $request)
    {
        if (Auth::user()->setting_role_id != 11 )
        {
            return response()->json(['success' => false]);
        }
        try {
            $payroll = SatellitePaymentPayroll::where('payment_date', $request->payment_date)->where('owner_id', $request->owner_id)->first();

            $mail = SatelliteTemplateStatistic::first();
            $payroll_send['payroll'][0]["payment_date"] = $payroll->payment_date;
            $payroll_send['payroll'][0]["payment_range"] = $payroll->payment_range;
            $payroll_send['payroll'][0]["total"] = $payroll->total;
            $payroll_send['payroll'][0]["percent_gb"] = $payroll->percent_gb;
            $payroll_send['payroll'][0]["percent_studio"] = $payroll->percent_studio;
            $payroll_send['payroll'][0]["trm"] = $payroll->trm;
            $payroll_send['payroll'][0]["transaction"] = $payroll->transaction;
            $payroll_send['payroll'][0]["retention"] = $payroll->retention;
            $payroll_send['payroll'][0]["payment"] = $payroll->payment;

            Mail::to("romangbmediagroup@gmail.com")->send(new OwnerStatistic($mail, $payroll->id ,$payroll_send, $payroll->owner_id, $request->payment_date));

            return response()->json(['success' => true]);
        }
        catch (Exception $e) {
            return response()->json(['success' => false]);
        }
    }

    public function statisticSummary(Request $request)
    {
        $result = "<table class='table table-hover'>
                    <thead>
                        <tr>
                            <th>Propietario</th>
                            <th>Página</th>
                            <th>Nick</th>
                            <th>Valor</th>
                            <th>Descripción</th>
                        </tr>
                    </thead>
                    <tbody>";
        $payment_accounts = SatellitePaymentAccount::where('account_id', $request->id)->orderBy('payment_date', 'DESC')->get();

        foreach ($payment_accounts as $payment)
        {
            $result .= "<tr>";
            $result .= "<td>".$payment->owner->owner."</td>";
            $result .= "<td>".$payment->page->name."</td>";
            $result .= "<td>".$payment->nick."</td>";
            $result .= "<td class='text-success'> $ ".$payment->amount."</td>";
            $file = SatellitePaymentFile::select('start_date', 'end_date')->where('id', $payment->file_id)->first();
            $result .= "<td>".$file->start_date." al ".$file->end_date."</td>";
            $result .= "</tr>";

        }
        $result .= "</tbody></table>";

        return $result;
    }

    public function updatePersonalInfo(Request $request)
    {
        $this->folderExists('satellite/owner');
        $this->validate($request,
        [
            'owner' => 'required|unique:satellite_owners,owner,'.$request->id,
            'email' => 'required|unique:satellite_owners,email,'.$request->id,
            'document_number' => 'nullable|numeric',
            'phone' => 'nullable|numeric',
        ],
        [
            'owner.required' => 'Este campo es obligatorio',
            'owner.unique' => 'El propietario ya existe',
            'email.required' => 'Este campo es obligatorio',
            'email.unique' => 'Ya existe un propietario con ese email',
            'document_number.numeric' => 'Este campo debe ser numerico',
            'phone.numeric' => 'Este campo debe ser numerico',
        ]);

        try {
            DB::beginTransaction();

            $owner = SatelliteOwner::find($request->id);
            $owner->owner = $request->owner;
            $owner->email = $request->email;
            $owner->first_name = $request->first_name;
            $owner->second_name = $request->second_name;
            $owner->last_name = $request->last_name;
            $owner->second_last_name = $request->second_last_name;
            $owner->document_number = $request->document_number;
            $owner->phone = $request->phone;
            $owner->others_emails = $request->others_emails;
            $owner->statistics_emails = $request->statistics_emails;
            $owner->department_id = $request->department;
            $owner->city_id = $request->city;
            $owner->address = $request->address;
            $owner->neighborhood = $request->neighborhood;
            $owner->save();

            if ($request->file('rut'))
            {
                $files = $request->file('rut');
                foreach($files as $file){
                    $owner_documentation = new SatelliteOwnerDocumentation;
                    $owner_documentation->owner = $owner->id;
                    $owner_documentation->type = 1;
                    $owner_documentation->file = $this->uploadFile($file, 'satellite/owner');
                    $owner_documentation->save();
                }
            }

            if ($request->file('chamber_commerce'))
            {
                $files = $request->file('chamber_commerce');
                foreach($files as $file){
                    $owner_documentation = new SatelliteOwnerDocumentation;
                    $owner_documentation->owner = $owner->id;
                    $owner_documentation->type = 2;
                    $owner_documentation->file = $this->uploadFile($file, 'satellite/owner');
                    $owner_documentation->save();
                }
            }

            if ($request->file('shareholder_structure'))
            {
                $files = $request->file('shareholder_structure');
                foreach($files as $file){
                    $owner_documentation = new SatelliteOwnerDocumentation;
                    $owner_documentation->owner = $owner->id;
                    $owner_documentation->type = 3;
                    $owner_documentation->file = $this->uploadFile($file, 'satellite/owner');
                    $owner_documentation->save();
                }
            }

            if ($request->file('bank_certification'))
            {
                $files = $request->file('bank_certification');
                foreach($files as $file){
                    $owner_documentation = new SatelliteOwnerDocumentation;
                    $owner_documentation->owner = $owner->id;
                    $owner_documentation->type = 4;
                    $owner_documentation->file = $this->uploadFile($file, 'satellite/owner');
                    $owner_documentation->save();
                }
            }

            DB::commit();
            return response()->json(['success' => true, 'owner_id' => $owner->id]);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json(['success' => false]);
        }
    }

    public function updatePaymentMethod(Request $request)
    {

        if ($request->payment_method == 2 || $request->payment_method == 6 || $request->payment_method == 9)
        {
            $this->validate($request,
            [
                'holder' => 'required',
                'document_number' => 'required|numeric',
                'account_number' => 'required|numeric',
            ],
            [
                'holder.required' => 'Este campo es obligatorio',
                'document_number.required' => 'Este campo es obligatorio',
                'document_number.numeric' => 'Este campo debe ser numerico',
                'account_number.required' => 'Este campo es obligatorio',
                'account_number.numeric' => 'Este campo debe ser numerico',
            ]);
        }

        if ($request->payment_method == 3 || $request->payment_method == 5 )
        {
            $this->validate($request,
            [
                'holder' => 'required',
                'document_number' => 'required|numeric',
            ],
            [
                'holder.required' => 'Este campo es obligatorio',
                'document_number.required' => 'Este campo es obligatorio',
                'document_number.numeric' => 'Este campo debe ser numerico',
            ]);
        }

        if ($request->payment_method == 4) {
            $this->validate($request,
            [
                'holder' => 'required',
            ],
            [
                'holder.required' => 'Este campo es obligatorio',
            ]);
        }

        if ($request->payment_method == 7)
        {
            $this->validate($request,
            [
                'holder' => 'required',
                'bank_usa' => 'required',
                'document_number' => 'required|numeric',
                'account_number' => 'required|numeric',
            ],
            [
                'holder.required' => 'Este campo es obligatorio',
                'bank_usa.required' => 'Este campo es obligatorio',
                'document_number.required' => 'Este campo es obligatorio',
                'document_number.numeric' => 'Este campo debe ser numerico',
                'account_number.required' => 'Este campo es obligatorio',
                'account_number.numeric' => 'Este campo debe ser numerico',
            ]);
        }

        if ($request->payment_method == 8)
        {
            $this->validate($request,
            [
                'holder' => 'required',
                'document_number' => 'required|numeric',
            ],
            [
                'holder.required' => 'Este campo es obligatorio',
                'document_number.required' => 'Este campo es obligatorio',
                'document_number.numeric' => 'Este campo debe ser numerico',
            ]);
        }

        try {
            DB::beginTransaction();

            $owner = SatelliteOwner::find($request->id);
            $owner->payment_method = $request->payment_method;
            $owner->save();

            SatelliteOwnerPaymentInfo::where('owner', $owner->id)->delete();

            $payment_info = new SatelliteOwnerPaymentInfo;
            $payment_info->owner = $owner->id;
            $payment_info->holder = $request->holder;

            if ($request->payment_method != 1 && $request->payment_method != 4) {
                $payment_info->document_type = $request->document_type;
                $payment_info->document_number = $request->document_number;
            }

            //banco, banco sin retencion, banco regimen simple
            if ($request->payment_method == 2 || $request->payment_method == 6 || $request->payment_method == 9)
            {
                $payment_info->bank = $request->bank;
                $payment_info->account_type = $request->account_type;
                $payment_info->account_number = $request->account_number;
                $payment_info->city_id = $request->city;
            }
            //efecty, cheque
            if ($request->payment_method == 3 || $request->payment_method == 5)
            {
                $payment_info->city_id = $request->city;
                $payment_info->address = $request->address;
                $payment_info->phone = $request->phone;

            }
            //banco usa
            if ($request->payment_method == 7)
            {
                $payment_info->bank_usa = $request->bank_usa;
                $payment_info->account_type = $request->account_type;
                $payment_info->account_number = $request->account_number;
                $payment_info->city_id = $request->city;
            }
            //western union
            if ($request->payment_method == 8)
            {
                $payment_info->city_id = $request->city;
                $payment_info->address = $request->address;
                $payment_info->phone = $request->phone;
                $payment_info->country = $request->country;
            }

            $payment_info->save();

            //historial de columnas modificadas, falta
            /*$changes = $payment_info->getChanges();
            $fields = [
                "owner_id" => "Propietario",
                "page_id" => "Pagina",
                "status_id" => "Estado",
                "nick" => "Nick",
                "first_name" => "Nombre",
                "second_name" => "Segundo Nombre",
                "last_name" => "Primer Apellido",
                "second_last_name" => "Segundo Nombre",
                "birth_date" => "Fecha Nacimiento",
                "access" => "Email",
                "password" => "Clave",
                "live_id" => "Live ID",
                "comment" => "Comentario",
            ];

            foreach ($changes as $key => $change) {
                if ($key != "updated_at" && $key != "modified_by")
                {
                    $log = new SatelliteAccountLog;
                    $log->type = $fields[$key];
                    $log->account_id = $account->id;
                    $log->action = "modificado";

                    $previous = $original[$key];
                    $now = $change;

                    if ($key == "owner_id") {
                        $previous = SatelliteOwner::select('owner')->where('id', $original[$key])->get();
                        $previous = $previous[0]->owner;
                        $now = SatelliteOwner::select('owner')->where('id', $change)->get();
                        $now = $now[0]->owner;
                    }
                    elseif($key == "page_id"){
                        $previous = SatelliteTemplatesPagesField::select('name')->where('id', $original[$key])->get();
                        $previous = $previous[0]->name;
                        $now = SatelliteTemplatesPagesField::select('name')->where('id', $change)->get();
                        $now = $now[0]->name;
                    }
                    elseif($key == "status_id"){
                        $previous = SatelliteAccountStatus::select('name')->where('id', $original[$key])->get();
                        $previous = $previous[0]->name;
                        $now = SatelliteAccountStatus::select('name')->where('id', $change)->get();
                        $now = $now[0]->name;
                    }

                    $log->previous = $previous;
                    $log->now = $now;

                    $log->created_by = Auth::user()->id;
                    $log->save();
                }

            }*/

            DB::commit();
            return response()->json(['success' => true, 'owner_id' => $owner->id]);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json(['success' => false]);
        }
    }

    public function updateCommission(Request $request)
    {
        $this->validate($request,
        [
            'percent' => 'required|numeric',
        ],
        [
            'percent.required' => 'Este campo es obligatorio',
            'percent.numeric' => 'Este campo debe ser numerico',
        ]);

        $page = null;
        if ($request->type != 1)
        {
            $this->validate($request,
            [
                'page' => 'required',
            ],
            [
                'page.required' => 'Este campo es obligatorio para esta opción',
            ]);
            $page = $request->page;
        }

        try {
            DB::beginTransaction();

            $commission = SatelliteOwnerCommissionRelation::find($request->commission_id);
            $commission->owner_receiver = $request->owner_receiver;
            $commission->percent = $request->percent;
            $commission->type = $request->type;
            $commission->page = $page;
            $commission->save();

            DB::commit();
            return response()->json(['success' => true]);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json(['success' => false]);
        }
    }

    public function updateApi(Request $request)
    {
        try {
            DB::beginTransaction();
            $tenant_owner = Tenant::where('data->owner_id', $request->owner_id)->first();
            if ($tenant_owner != null)
            {
                $tenant_owner->owner_id = 0;
                $tenant_owner->save();
            }

            if ($request->tenant != 0)
            {
                $tenant = Tenant::find($request->tenant);
                $tenant->owner_id = $request->owner_id;
                $tenant->save();
            }

            DB::commit();
            return response()->json(['success' => true]);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json(['success' => false]);
        }
    }

    public function updatePercent(Request $request)
    {
        $this->validate($request,
        [
            'commission_percent' => 'required|numeric',
        ],
        [
            'commission_percent.required' => 'Este campo es obligatorio',
            'commission_percent.numeric' => 'Este campo debe ser numerico',
        ]);

        try {
            DB::beginTransaction();

            $owner = SatelliteOwner::find($request->owner_id);
            $owner->commission_percent = $request->commission_percent;
            $owner->save();

            DB::commit();
            return response()->json(['success' => true]);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json(['success' => false]);
        }
    }

    public function updateStatus(Request $request)
    {
        $status_comment = "";
        $convert = false;
        if ($request->status == 2)
        {
            $this->validate($request,
            [
                'status_comment' => 'required',
            ],
            [
                'status_comment.required' => 'Este campo es obligatorio',
            ]);

            $status_comment = $request->status;
        }

        $owner = SatelliteOwner::find($request->id);
        $owner->status = $request->status;;
        $owner->status_comment = $status_comment;
        $owner->user_manager = $request->user_manager;
        $owner->save();

        if (isset($request->convert))
        {
            $owner->is_user = 0;
            $owner->commission_percent = 80;
            $owner->save();

            SatelliteAccount::where('owner_id', $owner->id)->update(['owner_id' => 2]);
            $convert = true;
        }

        return response()->json(['success' => true, 'convert' => $convert]);
    }

    public function updateUser(Request $request)
    {
        $exists = SatelliteUser::where('document_type', $request->document_type)
        ->where('document_number', $request->document_number)
        ->where('country_id', $request->country_id )
        ->where('id', "!=" ,$request->user_id)
        ->exists();

        if ($exists) {
            return response()->json(
            [
                'success' => false,
                'exists' => true,
            ]);
        }

        $this->folderExists('satellite/user');
        $this->validate($request,
        [
            'document_type' => 'required',
            'document_number' => 'required|numeric',
            'first_name' => 'required',
            'last_name' => 'required',
            'birth_date' => 'required',
        ],
        [
            'document_type.required' => 'Este campo es obligatorio',
            'document_number.required' => 'Este campo es obligatorio',
            'document_number.numeric' => 'Este campo debe ser numerico',
            'first_name.required' => 'Este campo es obligatorio',
            'last_name.required' => 'Este campo es obligatorio',
            'birth_date.required' => 'Este campo es obligatorio',
        ]);

        try {
            DB::beginTransaction();

            $user = SatelliteUser::find($request->user_id);
            $user->first_name = $request->first_name;
            $user->second_name = $request->second_name;
            $user->last_name = $request->last_name;
            $user->second_last_name = $request->second_last_name;
            $user->document_type = $request->document_type;
            $user->document_number = $request->document_number;
            $country_id = ($request->document_type <= 3)? 49 : $request->country_id;
            $user->country_id = $country_id;
            $user->birth_date = $request->birth_date;
            $user->modified_by = Auth::user()->id;
            $user->save();

            if ($request->file('front_image'))
            {
                $old_image = SatelliteUsersImage::where('satellite_user_id', $request->user_id)->where('type', 1)->first();
                $file = $request->file('front_image');
                $user_image = new SatelliteUsersImage;
                $user_image->satellite_user_id = $user->id;
                $user_image->type = 1;
                $user_image->image = $this->uploadFile($file, 'satellite/user');
                $user_image->save();
                if ($old_image != null) {
                    unlink("storage/GB/satellite/user/$old_image->image");
                    $old_image->delete();
                }
            }

            if ($request->file('back_image'))
            {
                $old_image = SatelliteUsersImage::where('satellite_user_id', $request->user_id)->where('type', 2)->first();
                $file = $request->file('back_image');
                $user_image = new SatelliteUsersImage;
                $user_image->satellite_user_id = $user->id;
                $user_image->type = 2;
                $user_image->image = $this->uploadFile($file, 'satellite/user');
                $user_image->save();
                if ($old_image != null) {
                    unlink("storage/GB/satellite/user/$old_image->image");
                    $old_image->delete();
                }
            }

            if ($request->file('holding_image'))
            {
                $old_image = SatelliteUsersImage::where('satellite_user_id', $request->user_id)->where('type', 3)->first();
                $file = $request->file('holding_image');
                $user_image = new SatelliteUsersImage;
                $user_image->satellite_user_id = $user->id;
                $user_image->type = 3;
                $user_image->image = $this->uploadFile($file, 'satellite/user');
                $user_image->save();
                if ($old_image != null) {
                    unlink("storage/GB/satellite/user/$old_image->image");
                    $old_image->delete();
                }

            }

            if ($request->file('profile_image'))
            {
                $old_image = SatelliteUsersImage::where('satellite_user_id', $request->user_id)->where('type', 4)->first();
                $file = $request->file('profile_image');
                $user_image = new SatelliteUsersImage;
                $user_image->satellite_user_id = $user->id;
                $user_image->type = 4;
                $user_image->image = $this->uploadFile($file, 'satellite/user');
                $user_image->save();
                if ($old_image != null) {
                    unlink("storage/GB/satellite/user/$old_image->image");
                    $old_image->delete();
                }
            }

            DB::commit();
            return response()->json(
            [
                'success' => true,
                'exists' => false,
            ]);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json(
            [
                'success' => false,
                'exists' => false,
            ]);
        }
    }

    public function updateConfig(Request $request)
    {
        $this->validate($request,
        [
            'subject' => 'required',
            'body' => 'required',
        ],
        [
            'subject.required' => 'Este campo es obligatorio',
            'body.required' => 'Este campo es obligatorio',
        ]);

        try {
            DB::beginTransaction();

            $template = SatelliteTemplatesForEmail::updateOrCreate(
                [
                    'template_page_id' => $request->template_page_id
                ],
                [
                    'template_page_id' => $request->template_page_id,
                    'subject' => $request->subject,
                    'body' => $request->body,
                    'user_id' => Auth::user()->id,
                ]
            );

            $page = SatelliteTemplatesPagesField::find($request->template_page_id);
            $page->nick = (isset($request->nick))? 1 : 0 ;
            $page->full_name = (isset($request->full_name))? 1 : 0 ;
            $page->access = (isset($request->access))? 1 : 0 ;
            $page->password = (isset($request->password))? 1 : 0 ;
            $page->save();

            DB::commit();
            return response()->json(['success' => true,]);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json(['success' => false]);
        }
    }

    public function updateAccount(Request $request)
    {
        try {
            DB::beginTransaction();

            if ($request->status_id == 3 || $request->status_id == 5) {
                $this->validate($request,
                [
                    'comment' => 'required',
                ],
                [
                    'comment.required' => 'Este campo es obligatorio',
                ]);
            }

            $this->validate($request,
            [
                'nick' => ['required',Rule::unique('satellite_accounts')->where(function ($query) use ($request){
                    return $query->where('nick',$request->nick)->where('page_id',$request->page_id)->where('id', '!=', $request->account_id);
                })],
                'owner_placeholder' => 'required',
                'page_id' => 'required',
            ],
            [
                'nick.required' => 'Este campo es obligatorio',
                'nick.unique' => 'Esta cuenta ya existe',
                'owner_placeholder.required' => 'Este campo es obligatorio',
                'page_id.required' => 'Este campo es obligatorio',
            ]);


            $account = SatelliteAccount::find($request->account_id);
            $original = $account->getOriginal();
            $account->owner_id = $request->owner_id;
            $account->page_id = $request->page_id;
            $account->status_id = $request->status_id;
            $account->nick = $request->nick;
            $account->first_name = $request->first_name;
            $account->second_name = $request->second_name;
            $account->last_name = $request->last_name;
            $account->second_last_name = $request->second_last_name;
            $account->birth_date = $request->birth_date;
            $account->access = $request->access;
            $account->password = $request->password;
            $account->live_id = $request->live_id;
            $account->comment = $request->comment;
            $account->modified_by = Auth::user()->id;
            $account->save();
            $changes = $account->getChanges();

            //historial de columnas modificadas
            $fields = [
                "owner_id" => "Propietario",
                "page_id" => "Pagina",
                "status_id" => "Estado",
                "nick" => "Nick",
                "first_name" => "Nombre",
                "second_name" => "Segundo Nombre",
                "last_name" => "Primer Apellido",
                "second_last_name" => "Segundo Nombre",
                "birth_date" => "Fecha Nacimiento",
                "access" => "Email",
                "password" => "Clave",
                "live_id" => "Live ID",
                "comment" => "Comentario",
            ];

            foreach ($changes as $key => $change) {
                if ($key != "updated_at" && $key != "modified_by")
                {
                    $log = new SatelliteAccountLog;
                    $log->type = $fields[$key];
                    $log->account_id = $account->id;
                    $log->action = "modificado";

                    $previous = $original[$key];
                    $now = $change;

                    if ($key == "owner_id") {
                        $previous = SatelliteOwner::select('owner')->where('id', $original[$key])->get();
                        $previous = $previous[0]->owner;
                        $now = SatelliteOwner::select('owner')->where('id', $change)->get();
                        $now = $now[0]->owner;
                    }
                    elseif($key == "page_id"){
                        $previous = SatelliteTemplatesPagesField::select('name')->where('id', $original[$key])->get();
                        $previous = $previous[0]->name;
                        $now = SatelliteTemplatesPagesField::select('name')->where('id', $change)->get();
                        $now = $now[0]->name;
                    }
                    elseif($key == "status_id"){
                        $previous = SatelliteAccountStatus::select('name')->where('id', $original[$key])->get();
                        $previous = $previous[0]->name;
                        $now = SatelliteAccountStatus::select('name')->where('id', $change)->get();
                        $now = $now[0]->name;
                    }

                    $log->previous = $previous;
                    $log->now = $now;

                    $log->created_by = Auth::user()->id;
                    $log->save();
                }

            }

            //parejas
            $partner_array = json_decode($request->partner);
            if (count($partner_array) > 0)
            {
                for ($i=0; $i < count($partner_array) ; $i++) {
                    if ($partner_array[$i]->id == 0 && $partner_array[$i]->deleted == 0)
                    {
                        $partner = new SatelliteAccountPartner;
                        $partner->account_id = $account->id;
                        $partner->name = $partner_array[$i]->name;
                        $partner->save();

                        $log = new SatelliteAccountLog;
                        $log->type = "Pareja";
                        $log->account_id = $account->id;
                        $log->action = "creado";
                        $log->previous = "";
                        $log->now = $partner_array[$i]->name;
                        $log->created_by = Auth::user()->id;
                        $log->save();

                    }
                    if ($partner_array[$i]->id != 0 && $partner_array[$i]->deleted == 1) {
                        SatelliteAccountPartner::where('id', $partner_array[$i]->id)->delete();

                        $log = new SatelliteAccountLog;
                        $log->type = "Pareja";
                        $log->account_id = $account->id;
                        $log->action = "eliminado";
                        $log->previous = $partner_array[$i]->name;
                        $log->now = "";
                        $log->created_by = Auth::user()->id;
                        $log->save();
                    }
                }
            }

            //verificando si la cuenta esta en Resumen de Archivos Subidos
            $file = SatellitePaymentFile::select('payment_date', 'trm')->orderBy('payment_date', 'desc')->first();
            if ($file != null)
            {
                $payment_accounts = SatellitePaymentAccount::where('owner_id', null)->where('page_id', $account->page_id)
                    ->where('nick', $account->nick)->where('payment_date', $file->payment_date)->get();

                foreach ($payment_accounts as $key => $payment_account) {

                    if ($account->status_id == 1 || $account->status_id == 6 || $account->status_id == 8) {
                        $account->status_id = 2;
                        $account->save();
                    }

                    $payment_account->owner_id = $account->owner_id;
                    $payment_account->account_id = $account->id;
                    $payment_account->save();
                    $owner = SatelliteOwner::select('owner')->where('id', $account->owner_id)->get();

                    $receivers = SatelliteOwnerCommissionRelation::select('owner_receiver')->distinct('owner_receiver')->where('owner_giver', $account->owner_id)->get();

                    foreach ($receivers as $receiver) {
                        $this->createCommisionForReceiver($receiver->owner_receiver, $account->owner_id, $payment_account->payment_date);
                    }

                    $payment_account_alert = [
                        "id" => $payment_account->id,
                        "owner_id" => $payment_account->owner_id,
                        "owner_name" => $owner[0]->owner,
                        "account_id" => $payment_account->account_id,
                    ];
                    event(new PaymentAccount($payment_account_alert));
                }
            }

            $send_email = true;
            $email_send_status = 0;
            $owner_email = '';

            if($account->email_activated_sent != 1) {
                if (isset($changes['status_id'])) {
                    if ($changes['status_id'] == 2) { // active
                        //$page_field = SatelliteTemplatesPagesField::where('page_id', $account->page_id)->where('template_type_id', 2)->first(); // Active
                        $page_template = SatelliteTemplatesForEmail::where('template_page_id', $account->page_id)->where('template_type_id', 2)->with('fields')->first(); // Active

                        $send_email = true;
                        $email_send_status = 0;

                        if ($page_template->fields->full_name == 1 && ($request->first_name == "" || $request->last_name == "")) {
                            $send_email = false;
                            $email_send_status = 4;
                        }

                        if ($page_template->fields->access == 1 && $request->access == "") {
                            $send_email = false;
                            $email_send_status = 4;
                        }

                        if ($page_template->fields->password == 1 && $request->password == "") {
                            $send_email = false;
                            $email_send_status = 4;
                        }

                        if ($page_template->fields->count() <= 0) {
                            $send_email = false;
                            $email_send_status = 3;
                        }

                        if
                        (
                            preg_match("/^\.+$/", $request->first_name) ||
                            preg_match("/^\.+$/", $request->last_name) ||
                            preg_match("/^\.+$/", $request->first_name) ||
                            preg_match("/^\.+$/", $request->last_name)
                        ) // Si el nombre u apellido tiene solo comas o puntos, no enviar el correo
                        {
                            $send_email = false;
                            $email_send_status = 1;
                        }

                        if ($request->account_type == "with_user") {
                            $account->satellite_user_id = $request->satellite_user_id;
                        }

                        if ($send_email) {
                            $owner = SatelliteOwner::select('email')->where('id', $request->owner_id)->first();
                            $page = SettingPage::select('name')->where('id', $account->page_id)->first();

                            if (is_null($owner)) { // No owner
                                $email_send_status = 2;
                            } else {
                                $owner_email = $owner->email;

                                $full_name = $request->first_name . " " . $request->second_name . " " . $request->last_name . " " . $request->second_last_name;
                                $mail['subject'] = $page_template->subject;
                                $mail['pagina'] = $page->name;

                                $body = $page_template->body;
                                $body = str_replace("{{email}}", $request->email, $body);
                                $body = str_replace("{{nick}}", $request->nick, $body);
                                $body = str_replace("{{full_name}}", $full_name, $body);
                                $body = str_replace("{{access}}", $request->access, $body);
                                $body = str_replace("{{password}}", $request->password, $body);
                                $mail['body'] = $body;
                                $sent = Mail::to($owner->email)->send(new ActivatedAccount($mail));
                                //$sent = Mail::to("manuelgbmediagroup@gmail.com")->send(new ActivatedAccount($mail));
                                $account->email_activated_sent = $send_email;
                                $account->save();
                            }
                        }
                    }
                }

            }

            DB::commit();
            return response()->json(['success' => true, 'email_sent' => $send_email, 'email_send_status' => $email_send_status, 'owner_email' => $owner_email]);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json(['success' => false]);
        }
    }

    public function uploadImage(Request $request)
    {
        if ($request->file('file'))
        {
            $file = $request->file('file');
            $imgpath = asset("storage/GB/satellite/template/".$this->uploadFile($file, 'satellite/template'));
        }
        return response()->json(
            [
                'location' => $imgpath,
            ]);
    }

    public function uploadImageEmail(Request $request)
    {
        if ($request->file('file'))
        {
            $file = $request->file('file');
            $imgpath = asset("storage/GB/satellite/statisticMail/".$this->uploadFile($file, 'satellite/statisticMail'));
        }
        return response()->json(
            [
                'location' => $imgpath,
            ]);
    }

    public function uploadPayment(Request $request)
    {
        $this->validate($request,
        [
            'payment_date' => 'required',
        ],
        [
            'payment_date.required' => 'Este campo es obligatorio',
        ]);

        $pages = SatellitePaymentPage::where('status', 1)->get();
        foreach ($pages as $page)
        {
            $i = $page->id - 1 ;
            if ($request->file($i))
            {
                if ($page->has_euro == 1) {
                    $field = 'euro-'.$i;
                    $field_required = 'euro-'.$i.".required";

                    $this->validate($request,
                    [
                        $field => 'required',
                    ],
                    [
                        $field_required => 'Este campo es obligatorio',
                    ]);
                }
            }
        }

        try {
            DB::beginTransaction();

            $exists = SatellitePaymentFile::where('payment_date', '!=' ,$request->payment_date)->where('trm', null)->exists();

            if ($exists) {
                return response()->json(['success' => false, 'trm_null' => true]);
            }
            foreach ($pages as $page)
            {
                $i = $page->id - 1 ;
                $name = strtolower($page->name);
                $name = str_replace(" ", "_", $name);
                if ($request->file($i))
                {
                    $file = $request->file($i);
                    $file_uploaded = $this->uploadFile($file, 'satellite/upload/'.$name);
                    $imgpath = asset("storage/GB/satellite/upload/".$name."/".$file_uploaded);

                    $payment_file =  new SatellitePaymentFile;
                    $payment_file->payment_date = $request->payment_date;
                    $payment_file->start_date = $request['start_date-'.$i];
                    $payment_file->end_date = $request['end_date-'.$i];
                    $payment_file->page_id = $page->id;
                    $payment_file->file_url = $file_uploaded;
                    $payment_file->euro = $request['euro-'.$i];
                    $payment_file->created_by = Auth::user()->id;
                    $payment_file->save();

                    if ($page->type == "excel") {
                        Excel::import(new PaymentExcel($page, $payment_file), $file);
                    }
                    if ($page->type == "csv" && $page->id != 17) {
                        Excel::import(new PaymentCSV($page, $payment_file), $file);
                    }
                    if ($page->type == "csv" && $page->id == 17) {
                        Excel::import(new PaymentCSVSkyprivate($page, $payment_file), $file);
                    }
                    if ($page->type == "text") {
                        Excel::import(new PaymentText($page, $payment_file), $file);
                    }
                }

            }

            DB::Commit();
            return response()->json(['success' => true]);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json(['success' => false]);
        }


        /*if ($request->file('file'))
        {
            $file = $request->file('file');
            $imgpath = asset("storage/GB/satellite/template/".$this->uploadFile($file, 'satellite/template'));
        }
        return response()->json(
            [
                'location' => $imgpath,
            ]);*/
    }

    public function updateStatisticEmail(Request $request)
    {
        try {
             DB::beginTransaction();
                $result = SatelliteTemplateStatistic::find(1);
                $result->subject = $request->subject;
                $result->password = $request->password;
                $result->topic = $request->topic;
                $result->header = $request->header;
                $result->title1 = $request->title1;
                $result->section1 = $request->section1;
                $result->title2 = $request->title2;
                $result->section2 = $request->section2;
                $result->title3 = $request->title3;
                $result->section3 = $request->section3;
                $result->color1 = $request->color1;
                $result->color2 = $request->color2;
                $result->studio = $request->studio;
                $result->sign = $request->sign;
                $result->url_web = $request->url_web;
                $result->phone = $request->phone;
                $result->cell = $request->cell;
                $result->skype = $request->skype;
                $result->facebook = $request->facebook;
                $result->twitter = $request->twitter;
                $result->instagram = $request->instagram;
                $result->linkedin = $request->linkedin;
                $result->pinterest = $request->pinterest;
                $result->save();
             DB::commit();
            return response()->json(["success" => true]);
        }
        catch (Exception $e){
            DB::rollback();
            return response()->json(["success" => false]);
        }
    }

    public function viewPayrollOwner(Request $request)
    {
        $owner = SatelliteOwner::find($request->id);
        $user_permission = Auth()->user()->getPermissionsViaRoles()->pluck('name');
        return view("adminModules.satellite.payment.payroll", compact(['owner', 'user_permission']));
    }

    public function viewDebts(Request $request)
    {
        $user_permission = Auth()->user()->getPermissionsViaRoles()->pluck('name');
        return view("adminModules.satellite.debt.list")->with(compact(['user_permission']));
    }

    public function viewAccumulations()
    {
        return view("adminModules.satellite.accumulation.list");
    }

    public function viewEarnings()
    {
        return view("adminModules.satellite.earning.list");
    }

    public function viewContracts()
    {
        $user_permission = Auth()->user()->getPermissionsViaRoles()->pluck('name');
        return view("adminModules.satellite.contract.list")->with(compact(['user_permission']));
    }

    public function viewProspects()
    {
        $user_permission = Auth()->user()->getPermissionsViaRoles()->pluck('name');
        return view("adminModules.satellite.prospect.list")->with(compact(['user_permission']));
    }


    // by Ludwig
    public function buildAndSend(Request $request)
    {
        $owner = SatelliteOwner::find($request->owner_id);
        if(!empty($owner)){
           $owner_email = $owner->email;
           $owner_stats_emails = $owner->statistics_emails;
           $emails = explode(',', $owner_stats_emails);
           array_push($emails, $owner_email);
        }else{
            $msg = "No se pudo enviar";
            $code = 500;

            return response()->json([
                'msg' => $msg,
                'code' => $code,
            ]);
        }

        $payroll = SatellitePaymentPayroll::where('payment_date', $request->payment_date)->where('owner_id', $request->owner_id)->first();
        $accounts_send[0] = [];
        $payroll_accounts = SatellitePaymentAccount::where('payroll_id', $payroll->id)->get();

        foreach ($payroll_accounts as $key => $payroll_account)
        {
            $accounts_send[$key]["payment_date"] = $payroll_account->payment_date;
            $accounts_send[$key]["page"] = $payroll_account->page->name;
            $accounts_send[$key]["nick"] = $payroll_account->nick;
            $accounts_send[$key]["amount"] = $payroll_account->amount;
            $accounts_send[$key]["description"] = $payroll_account->description;
        }

        $commission_send[0] = [];
        $commissions = SatellitePaymentCommission::where('payroll_id', $payroll->id)->get();
        foreach ($commissions as $key => $commission)
        {
            $commission_send[$key]["amount"] = $commission->amount;
            $commission_send[$key]["assign_to"] = ($commission->assign_to == 2)? "Pesos" : "Dolares";
            $commission_send[$key]["description"] = $commission->description;
        }

        $deduction_send[0] = [];
        $deductions = SatellitePaymentDeduction::where([
            ['owner_id', $request->owner_id],
            ['payment_date', null]
        ])->orWhere([
            ['owner_id', $request->owner_id],
            ['payment_date', '<=' , $request->payment_date],
            ['finished_date', '>=' , $request->payment_date],
            ['status', 1],
        ])->orWhere([
            ['owner_id', $request->owner_id],
            ['payment_date', '<=' , $request->payment_date],
            ['finished_date', null ],
            ['status', 0],
        ])->get();
        foreach ($deductions as $key => $deduction)
        {
            $deduction_send[$key]["created_at"] = date_format(date_create($deduction->created_at),"d M Y");
            $deduction_send[$key]["total"] = $deduction->total;
            $deduction_send[$key]["times_paid"] = $deduction->times_paid;
            $deduction_send[$key]["deduction_to"] = ($deduction->deduction_to == 2)? "Pesos" : "Dolares";
            $deduction_send[$key]["amount"] = $deduction->amount;
            $deduction_send[$key]["description"] = $deduction->description;
            $deduction_send[$key]["paydeduction"] = "";
            $paydeductions = SatellitePaymentPayDeduction::where('deduction_id', $deduction->id)->get();
            foreach ($paydeductions as $paydeduction)
            {
                if ($deduction_send[$key]["paydeduction"] == "")
                    $deduction_send[$key]["paydeduction"] = date_format(date_create($paydeduction->payment_date),"d M Y")." ($".$paydeduction->amount.")";
                else
                    $deduction_send[$key]["paydeduction"] = ", ".date_format(date_create($paydeduction->payment_date),"d M Y")." ($".$paydeduction->amount.")";
            }
        }

        $payroll_send['payroll'][0]["payment_date"] = $payroll->payment_date;
        $payroll_send['payroll'][0]["payment_range"] = $payroll->payment_range;
        $payroll_send['payroll'][0]["total"] = $payroll->total;
        $payroll_send['payroll'][0]["percent_gb"] = $payroll->percent_gb;
        $payroll_send['payroll'][0]["percent_studio"] = $payroll->percent_studio;
        $payroll_send['payroll'][0]["trm"] = $payroll->trm;
        $payroll_send['payroll'][0]["transaction"] = $payroll->transaction;
        $payroll_send['payroll'][0]["retention"] = $payroll->retention;
        $payroll_send['payroll'][0]["payment"] = $payroll->payment;

        $excel['payroll'] = $payroll_send;
        $excel['accounts'] = $accounts_send;
        $excel['commissions'] = $commission_send;
        $excel['deductions'] = $deduction_send;

        $path = "statistics/".$request->owner_id;
        if (!file_exists($path)) {
            Storage::disk('local')->makeDirectory($path);
        }
        Excel::store(new PayrollStatistic($excel), $path.'/Pago.xlsx');

        foreach ($emails as $email){
            if (!empty($email)){
                //Mail::to('romangbmediagroup@gmail.com')->send(new OwnerStatistics2($request->owner_id));
                Mail::to(trim($email))->send(new OwnerStatistics2($request->owner_id));
            }
        }

        $payroll->update([
            'mail_send' => 1
        ]);

        $unlink_path = "app/".$path."/Pago.xlsx";
        unlink(storage_path($unlink_path));

        $msg = "sent";
        $code = 200;

        return response()->json([
            'msg' => $msg,
            'code' => $code,
        ]);
    }

    public function viewApi()
    {
        return view("adminModules.satellite.api.list");
    }

    public function SaveApi(Request $request)
    {
        $this->validate($request,
            [
                'user' => 'required',
                'token' => 'required'
            ],
            [
                'user.required' => 'Este campo es obligatorio',
                'token.required' => 'Este campo es obligatorio',
            ]
        );


        $msg = "";
        $code = 0;
        $icon = "";
        try {
            DB::beginTransaction();

            $api = SatelliteApi::updateOrCreate([
                'user' => $request->input('user'),
                'access_token' => $request->input('token'),
                'type' => $request->input('type'),
            ]);

            if($api){
                $msg = "API guardada exitosamente";
                $code = 200;
                $icon = "success";
            }

            DB::commit();
        } catch (\Exception $ex){
            $msg = "API no pudo ser guardado, comuniquese con el admin".$ex->getMessage();
            $code = 500;
            $icon = "success";
            DB::rollBack();
        }

        return response()->json([
            'msg' => $msg,
            'code' => $code,
            'icon' => $icon,
        ]);
    }

    public function getApis($id)
    {
       $apis = SatelliteApi::where('type', $id)->get();
       return response()->json($apis);
    }

    public function apiJasmin(Request $request)
    {
        $authorization_bearer = "Authorization: Bearer " . $request->input('studio_token');
        $model = $request->input('model');
        $fromDate = $request->input('fromDate');
        $toDate = $request->input('toDate');

        $curl = curl_init("https://partner-api.modelcenter.jasmin.com/v1/reports/performers/$model?fromDate=$fromDate&toDate=$toDate");
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json', $authorization_bearer));
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        $result = curl_exec($curl);
        $result1 = json_decode($result);
        curl_close($curl);

        $data = [];
        if (!isset($result1->status)) {
            $data['simple_total'] = round(($result1->data->total->earnings->value), 2);
            $data['compound_total'] = round($result1->data->total->earnings->value + $result1->data->total->averageEarningPerHour->value, 2);
        }else{
            $data['simple_total'] = 0;
            $data['compound_total'] = 0;
        }

        return response()->json($data);
    }

    public function apiChaturbate(Request $request)
    {
        $studio = $request->input('studio_token');
        $username = $studio['api_user'];
        $token = $studio['api_value'];

        if ($request->get('option') === 1){
            $url = "https://chaturbate.com/affiliates/apistats/?username=$username&token=$token";
        }else{
            $fromDate = $request->input('fromDate');
            $toDate = $request->input('toDate');
            $explode_fromDate = explode('-', $fromDate);
            $explode_toDate = explode('-', $toDate);


            if ($request->get('selected') === 1){
                $search_criteria = 2;
                $url = "https://es.chaturbate.com/affiliates/apistats/?stats_breakdown=sub_account__username&campaign=&search_criteria=$search_criteria&period=0&date_day=$explode_fromDate[2]&date_month=$explode_fromDate[1]&date_year=$explode_fromDate[0]&username=$username&token=$token";
            }else{
                $search_criteria = 3;
                $url = "https://es.chaturbate.com/affiliates/apistats/?stats_breakdown=sub_account__username&campaign=&search_criteria=$search_criteria&period=0&start_date_day=$explode_fromDate[2]&start_date_month=$explode_fromDate[1]&start_date_year=$explode_fromDate[0]&end_date_day=$explode_toDate[2]&end_date_month=$explode_toDate[1]&end_date_year=$explode_toDate[0]&username=$username&token=$token";
            }
        }

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        $result  = curl_exec($ch);
        $result1 = json_decode($result);
        curl_close($ch);

        $data = [];
        $stats = $result1->stats;
        if (!empty($stats))
        {
            $msg = "Datos enviados";
            $code = 200;
            $icon = "success";
            for($row = 0; $row < count($result1->stats); $row++)
            {
                if ($result1->stats[$row]->program === 'Cashed-Out Tokens')
                {
                    $data['fields'][$row] = ['key' => $result1->stats[$row]->columns, 'label' => $result1->stats[$row]->columns];
                    if (isset($result1->stats[$row]))
                    {
                        for($i=0; $i < count($result1->stats[$row]->rows) ; $i++)
                        {
                            $data['items'][$i]['date'] = $result1->stats[$row]->rows[$i][0];
                            $data['items'][$i]['tokens'] = $result1->stats[$row]->rows[$i][1];
                            $data['items'][$i]['dollars'] = $result1->stats[$row]->rows[$i][2];
                        }
                    }
                }
            }
        }
        else{
            $msg = "No hay datos para ".$username;
            $code =  404;
            $icon = "error";
        }

        return response()->json([
            'data' => $data,
            'msg' => $msg,
            'code' => $code,
            'icon' => $icon,
        ]);
    }
    // by Ludwig


    public function viewAccounting()
    {
        return view("adminModules.satellite.accounting.list");
    }

    public function viewPaymentTemplate()
    {
        return view("adminModules.satellite.statisticMail.list");
    }

    public function verifyIfLastPaymentDate($payment_date)
    {
        $date = SatellitePaymentFile::select('payment_date')->orderBy('payment_date', 'desc')->first();
        return ($date->payment_date == $payment_date)? true : false;
    }

    public function recalculatingOwnerPayroll(Request $request)
    {

    }

    public function executeScript()
    {
        $msg = "DONE";
        $this->ownersData();
        //$this->ownersAccount();
        //$this->scriptPaymentFile();
        //$this->scriptPaymentAccount();
        //$this->scriptPaymentDeduction();
        //$this->scriptPaymentCommission();
        //$this->scriptPaymentPayDeduction();
        //$this->scriptPaymentPayroll();
        //$this->scriptOldPaymentAccount();
        //$this->scriptStatusAccounts();
        //me quede aqui
        //$this->scriptOwnerDeductionTimesPaid();
        //$msg = $this->scriptOwnerDeductions();
        //$msg = $this->scriptOwnerCommissionTo();
        //$msg = $this->scriptOwnerRut();
        //$msg = $this->scriptOwnerFileChamberCommerce();
        //$msg = $this->scriptOwnerFileBankCertification();
        //$msg = $this->scriptOwnerFileShareholderStructure();
        //$msg = $this->scriptUsers();
        //$msg = $this->scriptUsersFiles();
        //$this->scriptContract();
        //$this->modifiedByAccount();
        //$msg = $this->scriptCreateAllCommission();
        //$msg = $this->scriptSecPendientesStudios();
        //$msg = $this->scriptRollbackPayroll();
        return $msg;
    }

    //scripts  http://laravel.gbmediagroup.com/public/scripts/satellite/execute
    public function ownersData()
    {
        $min_id = 2418;
        $max_id = 2426;
        try {
            DB::beginTransaction();
            $owners = DB::connection('gbmedia')->table('sec_propietario')->whereBetween('pro_id', [$min_id, $max_id])->get();
            foreach ($owners as $value)
            {
                $pro_id = $value->pro_id;
                $exists = SatelliteOwner::where('old_id', $pro_id)->exists();
                if ($exists)
                {
                    $owner = SatelliteOwner::where('old_id', $pro_id)->first();
                }
                else
                {
                    $owner = new SatelliteOwner;
                }

                $owner->old_id = $value->pro_id;
                $owner->owner = $value->pro_nombre;
                $owner->email = $value->pro_email;
                $owner->first_name = $value->pro_user_nombre;
                $owner->second_name = $value->pro_user_seg_nombre;
                $owner->last_name = $value->pro_user_apellido;
                $owner->second_last_name = $value->pro_user_seg_apellido;
                $owner->document_number = $value->pro_nro_doc;
                $owner->phone = $value->pro_telefono;
                $owner->others_emails = $value->pro_otros_emails;
                $owner->statistics_emails = $value->pro_correo_est;
                if ($value->pro_ciudad > 1 && $value->pro_ciudad < 1100 )
                {
                    $city = City::find($value->pro_ciudad);
                    $owner->city_id = $value->pro_ciudad;
                    $owner->department_id = $city->department_id;

                }
                $owner->address = $value->pro_direccion;
                $owner->neighborhood = $value->pro_barrio;
                $owner->commission_percent = $value->xciento_predeterm;
                if ($value->forma_pago_brs == 1)
                {
                    $owner->payment_method = 9;
                }
                elseif($value->forma_pago > 1)
                {
                    $owner->payment_method = $value->forma_pago;
                }
                else
                {
                    $owner->payment_method = 1;
                }

                if ($value->gerente_cuenta > 0)
                {
                    $owner->user_manager = $value->gerente_cuenta;
                }
                $owner->status = ($value->estado == "activo")? 1 : (($value->estado == "vetado")? 2 : 3);
                $owner->status_comment = $value->comentario_vetado;
                $owner->is_user = $value->from_gb;
                $owner->user_id = $value->usuario_id;
                $owner->purchase_limit = $value->limite_compra;
                $owner->save();

                SatelliteOwnerPaymentInfo::where('owner', $owner->id)->delete();

                $payment_info = new SatelliteOwnerPaymentInfo;
                $payment_info->owner = $owner->id;

                //banco
                if ($value->forma_pago == 2 || $value->forma_pago == 6)
                {
                    $payment_info->holder = $value->titular_cuenta;

                    if ($value->nombre_banco != "")
                    {
                        $banco = $value->nombre_banco;


                        if ($banco == "BANCO DAVIPLATA")
                        {
                            $banco = "DAVIPLATA";
                        }
                        if ($banco == "ITAÃš")
                        {
                            $banco = "ITAÚ";
                        }
                        /*echo "<br>".$banco;*/
                        $bank = Bank::where('name', $banco)->get();
                        $payment_info->bank = $bank[0]->id;
                    }

                    if ($value->tipo_doc_titular == "CC")
                    {
                        $payment_info->document_type = 1;
                    }
                    if ($value->tipo_doc_titular == "NIT")
                    {
                        $payment_info->document_type = 3;
                    }
                    if ($value->tipo_doc_titular == "Cédula Extranjería")
                    {
                        $payment_info->document_type = 2;
                    }
                    if ($value->tipo_doc_titular == "Cédula Venezolana")
                    {
                        $payment_info->document_type = 2;
                    }
                    if ($value->tipo_doc_titular == "Pasaporte")
                    {
                        $payment_info->document_type = 4;
                    }
                    $payment_info->document_number = $value->cedula_titular;
                    $payment_info->account_type = ($value->tipo_cuenta == "Corriente") ? 2 : 1;
                    $payment_info->account_number = $value->nro_cuenta;
                    $payment_info->city_id = $value->ciudad_banco;
                }
                //efecty y cheque sin retencion
                elseif ($value->forma_pago == 3 || $value->forma_pago == 5)
                {
                    $payment_info->holder = $value->nombre_efecty;
                    if ($value->tipo_doc_titular == "CC")
                    {
                        $payment_info->document_type = 1;
                    }
                    if ($value->tipo_doc_titular == "NIT")
                    {
                        $payment_info->document_type = 3;
                    }
                    if ($value->tipo_doc_titular == "Cédula Extranjería")
                    {
                        $payment_info->document_type = 2;
                    }
                    if ($value->tipo_doc_titular == "Cédula Venezolana")
                    {
                        $payment_info->document_type = 2;
                    }
                    if ($value->tipo_doc_titular == "Pasaporte")
                    {
                        $payment_info->document_type = 4;
                    }
                    $payment_info->document_number = $value->cedula_titular;
                    $payment_info->city_id = $value->ciudad_banco;
                    $payment_info->address = $value->direccion_completa;
                    $payment_info->phone = $value->telefono_titular;

                }
                //paxum
                elseif ($value->forma_pago == 4)
                {
                    $payment_info->holder = $value->usuario_paxum;
                }
                //banco usa
                elseif ($value->forma_pago == 7)
                {
                    $payment_info->holder = $value->titular_cuenta;
                    if ($value->tipo_doc_titular == "CC")
                    {
                        $payment_info->document_type = 1;
                    }
                    if ($value->tipo_doc_titular == "NIT")
                    {
                        $payment_info->document_type = 3;
                    }
                    if ($value->tipo_doc_titular == "Cédula Extranjería")
                    {
                        $payment_info->document_type = 2;
                    }
                    if ($value->tipo_doc_titular == "Cédula Venezolana")
                    {
                        $payment_info->document_type = 2;
                    }
                    if ($value->tipo_doc_titular == "Pasaporte")
                    {
                        $payment_info->document_type = 4;
                    }
                    $payment_info->bank_usa = $value->nombre_banco;
                    $payment_info->document_number = $value->cedula_titular;
                    $payment_info->account_type = ($value->tipo_cuenta == "Corriente") ? 2 : 1;
                    $payment_info->account_number = $value->nro_cuenta;
                    $payment_info->city_id = $value->ciudad_banco;

                }
                //western union
                elseif ($value->forma_pago == 8)
                {
                    $payment_info->holder = $value->titular_cuenta;
                    if ($value->tipo_doc_titular == "CC")
                    {
                        $payment_info->document_type = 1;
                    }
                    if ($value->tipo_doc_titular == "NIT")
                    {
                        $payment_info->document_type = 3;
                    }
                    if ($value->tipo_doc_titular == "Cédula Extranjería")
                    {
                        $payment_info->document_type = 2;
                    }
                    if ($value->tipo_doc_titular == "Cédula Venezolana")
                    {
                        $payment_info->document_type = 2;
                    }
                    if ($value->tipo_doc_titular == "Pasaporte")
                    {
                        $payment_info->document_type = 4;
                    }
                    $payment_info->document_number = $value->cedula_titular;
                    $payment_info->city_id = $value->ciudad_banco;
                    $payment_info->address = $value->direccion_completa;
                    $payment_info->phone = $value->telefono_titular;
                }
                $payment_info->save();
            }

            DB::Commit();
            echo "con exito";
        } catch (Exception $e) {
            DB::rollback();
            echo "ha ocurrido un error";
        }
    }

    //falta poner los satellite_user_id y created_by
    public function ownersAccount()
    {
        $min_id = 53761;
        $max_id = 53986;
        try {
            DB::beginTransaction();
            $accounts = DB::connection('gbmedia')->table('sec')->whereBetween('sec_id', [$min_id, $max_id])->get();
            foreach ($accounts as $value)
            {
                $pro_id = $value->fk_pro_id;
                $pagina = $value->pagina;
                $nick = trim($value->nick);
                if ($pagina == "" || $pagina == "webcamclub" )
                {
                    continue;
                }

                $exists = SatelliteOwner::where('old_id', $pro_id)->exists();
                if ($exists)
                {
                    $pagina = ucfirst($pagina);
                    $page = SettingPage::where('name', $pagina)->get();
                    $page = $page[0]->id;
                    $owner = SatelliteOwner::where('old_id', $pro_id)->first();

                    $exists = SatelliteAccount::where('owner_id', $owner->id)->where('page_id', $page)->where('nick', $nick)->exists();
                    if ($exists)
                    {
                        $account = SatelliteAccount::where('owner_id', $owner->id)->where('page_id', $page)->where('nick', $nick)->first();
                    }
                    else{
                        $account = new SatelliteAccount;
                    }

                    $account->acc_old_id = $value->sec_id;
                    $account->owner_id = $owner->id;
                    $account->page_id = $page;
                    $status = $value->fk_sec_estado_id;
                    if ($status == 0)
                    {
                        $status = 1;
                    }
                    if ($status == 1)
                    {
                        $status = 2;
                    }

                    $account->status_id = $status;
                    $account->nick = $nick;
                    $account->original_nick = $nick;

                    $account->first_name = $value->nombre;
                    $account->second_name = $value->segundo_nombre;
                    $account->last_name = $value->apellido;
                    $account->second_last_name = $value->segundo_apellido;
                    if($value->fecha_nacimiento != "0000-00-00")
                    {
                        $account->birth_date = $value->fecha_nacimiento;
                    }

                    $account->access = $value->acceso1;
                    $account->password = $value->acceso2;
                    $account->live_id = $value->live_id;
                    $account->from_gb = $value->from_gb;
                    $account->satellite_user_id = $value->sec_user_id;
                    $account->comment = $value->sec_comentario_vetado;
                    $account->email_sent = ($value->email_creado_enviado > 0)? $value->email_creado_enviado : 0;
                    $account->modified_by = $value->modificado_por;

                    if($value->fecha_creacion != "0000-00-00")
                    {
                        $account->created_at = $value->fecha_creacion." 00:00:00";
                    }
                    $account->save();
                }
                else
                {
                    echo "<br> no existe el propietario".$pro_id;
                }

            }

            DB::Commit();
            return response()->json(['success' => true]);
        } catch (Exception $e) {
            DB::rollback();
            echo "ha ocurrido un error";
        }
    }

    public function scriptPaymentFile()
    {
        $min_id = 1821;
        $max_id = 1850;

        try {
            DB::beginTransaction();
            $array = DB::connection('gbmedia')->table('sec_archivo')->whereBetween('s_arc_id', [$min_id, $max_id])->get();
            foreach ($array as $value)
            {
                $s_arc_id = $value->s_arc_id;

                $exists = SatellitePaymentFile::where('old_id', $s_arc_id)->exists();
                if ($exists)
                {
                    $file = SatellitePaymentFile::where('old_id', $s_arc_id)->first();
                }
                else
                {
                    $file = new SatellitePaymentFile;
                }

                $pagina = strtolower($value->pagina);
                $pagina = ucwords($pagina);
                if ($pagina == "Streamate" && $value->tipo == "studio")
                {
                    $pagina = 3;
                }
                elseif ($pagina == "Streamate" && $value->tipo == "xbono")
                {
                    $pagina = 4;
                }
                elseif ($pagina == "Xlovecam" && $value->tipo == "xbono")
                {
                    $pagina = 12;
                }
                else {
                    echo "<br>".$pagina;
                    $pagina_search = SatellitePaymentPage::where('name', $pagina)->first();
                    $pagina = $pagina_search->id;
                }

                $file->old_id = $value->s_arc_id;
                $file->payment_date = $value->fecha_pago;
                $file->start_date = $value->fecha_inicio;
                $file->end_date = $value->fecha_fin;
                $file->page_id = $pagina;
                $file->file_url = $value->url_archivo;
                $file->trm = $value->trm;
                $file->euro = $value->valor_euro;
                $file->created_by = 72;
                $file->save();
            }

            DB::Commit();
            echo "con exito";
        } catch (Exception $e) {
            DB::rollback();
            echo "ha ocurrido un error";
        }
    }

    public function scriptPaymentAccount()
    {
        $min_id = 289869;
        $max_id = 294331;
        try {
            DB::beginTransaction();
            $array = DB::connection('gbmedia')->table('sec_asig')->whereBetween('s_asig_id', [$min_id, $max_id])->get();
            foreach ($array as $value)
            {
                $nick = trim($value->nick_arch);
                if ($value->sa_fk_pro_id == 0)
                {
                    continue;
                }
                $s_asig_id = $value->s_asig_id;
                $exists = SatellitePaymentAccount::where('old_s_asig_id', $s_asig_id)->exists();
                if ($exists)
                {
                    $payment_account = SatellitePaymentAccount::where('old_s_asig_id', $s_asig_id)->first();
                }
                else
                {
                    $payment_account = new SatellitePaymentAccount;
                }

                $payment_account->old_s_asig_id = $value->s_asig_id;
                $owner = SatelliteOwner::where('old_id', $value->sa_fk_pro_id)->first();
                $payment_account->owner_id = $owner->id;

                $file = SatellitePaymentFile::where('old_id', $value->fk_s_arc_id)->first();
                $page_id = $file->page_id;
                $page = SatellitePaymentPage::where('id', $page_id)->first();
                $payment_account->page_id = $page->setting_page_id;
                $account = SatelliteAccount::where('page_id', $page->setting_page_id)->where('nick', $nick)->first();
                if ($account == null)
                {
                    $account_id = null;
                }
                else
                {
                    $account_id = $account->id;
                }

                echo "<br> old account id ". $s_asig_id;

                $payment_account->account_id = $account_id;
                $payment_account->file_id = $file->id;
                $payment_account->nick = $nick;
                $payment_account->amount = $value->valor;
                $payment_account->old_xc = $value->xciento_predeterm;
                $payment_account->payment_date = $file->payment_date;
                $payment_account->live_id = $value->live_id;
                $payment_account->description = $value->descrip2." ".$value->fecha_bono." ".$value->descrip_bono." ".$value->week_bono;
                $payment_account->created_by = 72;
                $payment_account->save();
            }

            DB::Commit();
            echo "con exito";
        } catch (Exception $e) {
            DB::rollback();
            echo "ha ocurrido un error";
        }
    }

    //falta el last_pay y times_paid
    public function scriptPaymentDeduction()
    {
        $min_id = 10503;
        $max_id = 10650;
        try {
            DB::beginTransaction();
            $array = DB::connection('gbmedia')->table('sec_deducciones')->whereBetween('sec_ded_id', [$min_id, $max_id])->get();
            foreach ($array as $value)
            {
                $sec_ded_id = $value->sec_ded_id;
                $exists = SatellitePaymentDeduction::where('old_sec_ded_id', $sec_ded_id)->exists();
                if ($exists)
                {
                    $payment_deduction = SatellitePaymentDeduction::where('old_sec_ded_id', $sec_ded_id)->first();
                }
                else
                {
                    $payment_deduction = new SatellitePaymentDeduction;
                }

                $payment_deduction->old_sec_ded_id = $value->sec_ded_id;
                $payment_deduction->payment_date = $value->ded_fecha_pago;
                if($value->ded_fecha_fin != "0000-00-00")
                {
                    $payment_deduction->finished_date = $value->ded_fecha_fin;
                }

                $owner = SatelliteOwner::where('old_id', $value->sec_ded_fk_pro_id)->first();
                $payment_deduction->owner_id = $owner->id;
                $payment_deduction->deduction_to = ($value->ded_a == "Total $")? 0 : (($value->ded_a == "% Pago")? 1 : 2);
                $payment_deduction->total = $value->sec_ded_valor;
                $payment_deduction->times_paid = 0;
                $payment_deduction->amount = $value->sec_ded_valor;
                $payment_deduction->description = $value->sec_ded_comentario;
                $payment_deduction->type = ($value->ded_viene_de == 0)? 1 : $value->ded_viene_de;
                $payment_deduction->created_by = 3;
                $payment_deduction->status = $value->ded_terminada;
                if($value->ded_fecha_creacion != "0000-00-00")
                {
                    $payment_deduction->created_at = $value->ded_fecha_creacion." 00:00:00";
                }
                $payment_deduction->save();
            }

            DB::Commit();
            echo "con exito";
        } catch (Exception $e) {
            DB::rollback();
            echo "ha ocurrido un error";
        }
    }

    public function scriptPaymentCommission()
    {
        $min_id = 11236;
        $max_id = 11597;
        try {
            DB::beginTransaction();
            $array = DB::connection('gbmedia')->table('sec_suma')->whereBetween('sum_id', [$min_id, $max_id])->get();
            foreach ($array as $value)
            {
                $sum_id = $value->sum_id;
                $exists = SatellitePaymentCommission::where('old_sum_id', $sum_id)->exists();
                if ($exists)
                {
                    $payment_commission = SatellitePaymentCommission::where('old_sum_id', $sum_id)->first();
                }
                else
                {
                    $payment_commission = new SatellitePaymentCommission;
                }

                $payment_commission->old_sum_id = $value->sum_id;
                if ($value->sum_fecha_pago != '0000-00-00')
                {
                    $payment_commission->payment_date = $value->sum_fecha_pago;
                }
                $owner = SatelliteOwner::where('old_id', $value->sum_fk_pro_id)->first();
                $payment_commission->owner_id = $owner->id;
                $payment_commission->assign_to = ($value->sum_a == "Total $")? 0 : (($value->sum_a == "% Pago")? 1 : 2);
                $payment_commission->amount = $value->sum_valor;
                $payment_commission->description = $value->sum_descripcion;
                if ($value->comision_de != 0)
                {
                    $owner = SatelliteOwner::where('old_id', $value->sum_fk_pro_id)->first();
                    $coming_from = $owner->id;
                }
                else
                {
                    $coming_from = 0;
                }

                $payment_commission->coming_from = $coming_from;
                $payment_commission->created_by = 3;
                if($value->sum_fecha_creacion != "0000-00-00")
                {
                    $payment_commission->created_at = $value->sum_fecha_creacion." 00:00:00";
                }
                $payment_commission->save();
            }

            DB::Commit();
            echo "con exito";
        } catch (Exception $e) {
            DB::rollback();
            echo "ha ocurrido un error";
        }
    }

    public function scriptPaymentPayDeduction()
    {
        $min_id = 13242;
        $max_id = 13387;
        try {
            DB::beginTransaction();
            $array = DB::connection('gbmedia')->table('sec_abono')->whereBetween('ab_id', [$min_id, $max_id])->get();
            foreach ($array as $value)
            {
                $ab_id = $value->ab_id;
                $exists = SatellitePaymentPayDeduction::where('old_ab_id', $ab_id)->exists();
                if ($exists)
                {
                    $payment_paydeduction = SatellitePaymentPayDeduction::where('old_ab_id', $ab_id)->first();
                }
                else
                {
                    $payment_paydeduction = new SatellitePaymentPayDeduction;
                }

                $payment_paydeduction->old_ab_id = $value->ab_id;
                if ($value->ab_fecha_pago != '0000-00-00')
                {
                    $payment_paydeduction->payment_date = $value->ab_fecha_pago;
                }
                $owner = SatelliteOwner::where('old_id', $value->ab_fk_pro_id)->first();
                if ($owner == null)
                {
                    continue;
                }
                $payment_paydeduction->owner_id = $owner->id;
                $deduction = SatellitePaymentDeduction::where('old_sec_ded_id', $value->ab_fk_ded_id)->first();
                if ($deduction == null)
                {
                    continue;
                }
                $payment_paydeduction->deduction_id = $deduction->id;
                $payment_paydeduction->amount = $value->ab_valor;
                $payment_paydeduction->created_by = 3;
                if($value->ab_fecha_creacion != "0000-00-00")
                {
                    $payment_paydeduction->created_at = $value->ab_fecha_creacion." 00:00:00";
                }
                $payment_paydeduction->save();
            }

            DB::Commit();
            echo "con exito";
        } catch (Exception $e) {
            DB::rollback();
            echo "ha ocurrido un error";
        }
    }

    public function scriptOldPaymentAccount()
    {
        $min_id = 12;
        //$max_id = 2382;
        $max_id = 2382;
        $array = SatelliteOwner::whereBetween('old_id', [$min_id, $max_id])->get();
        foreach ($array as $row)
        {
            $old_id = $row->old_id;
            $acumulado = DB::connection('gbmedia')->table('sec_acumulado')->where('sec_ac_fk_prop_id', $old_id)->orderBy('sec_ac_fecha_pago', 'ASC')->first();
            if ($acumulado == null)
            {
                continue;
            }

            SatellitePaymentAccount::where('owner_id', $row->id)->where('payment_date', '<' ,$acumulado->sec_ac_fecha_pago)->delete();
        }
    }

    public function scriptPaymentPayroll()
    {
        //ultima ejecucion
        /*$min_id = 1797;
        $max_id = 2140;*/

        $min_id = 1099;
        $max_id = 1099;
        try {
            DB::beginTransaction();
            $array = SatelliteOwner::select('id', 'old_id')->whereBetween('id', [$min_id, $max_id])->where('is_user', 0)->get();
            //$array = SatelliteOwner::select('id', 'old_id')->whereBetween('id', [$min_id, $max_id])->where('is_user', 1)->get();
            //$array = SatelliteOwner::select('id', 'old_id')->whereBetween('id', [$min_id, $max_id])->get();
            $msg = "Error";
            foreach ($array as $value)
            {
                //$msg .= $this->scriptOwnerPayroll($value->id, $value->old_id);
                $msg .= $this->scriptOwnerPayroll2($value->id, $value->old_id);
                //$msg .= $this->scriptOwnerPayroll3($value->id, $value->old_id);
            }

            DB::Commit();
            echo "termina";
        } catch (Exception $e) {
            DB::rollback();
            echo "ha ocurrido un error";
        }
    }

    public function scriptOwnerPayroll($id, $old_id)
    {
            $dates = SatellitePaymentAccount::where('owner_id', $id)->distinct('payment_date')->get();
            $value = 0;
            foreach ($dates as $date)
            {
                $payment_date = $date->payment_date;
                $amount = SatellitePaymentAccount::where('owner_id', $id)->where('payroll_id', null)->where('payment_date', '<=' ,$payment_date)->sum('amount');
                $acumulado = DB::connection('gbmedia')->table('sec_acumulado')->where('sec_ac_fk_prop_id', $old_id)->where('sec_ac_fecha_pago', $payment_date)->get();
                if ($acumulado === null)
                {
                   continue;
                }

                if ($amount >= 65 || $acumulado[0]->sigue_acumulando == 3)
                {

                    if ($acumulado[0]->sigue_acumulando != 1 || $acumulado[0]->sigue_acumulando == 3)
                    {
                        $exists = SatellitePaymentPayroll::where('owner_id', $id)->where('payment_date', $payment_date)->exists();
                        if ($exists)
                        {
                            $payroll = SatellitePaymentPayroll::where('owner_id', $id)->where('payment_date', $payment_date)->first();
                        }
                        else
                        {
                            $payroll = new SatellitePaymentPayroll;
                        }

                        $owner = SatelliteOwner::find($id);

                        $file = SatellitePaymentFile::select('payment_date', 'trm')->where('payment_date', $payment_date)->first();

                        if ($file != null) {
                            $payroll->owner_id = $owner->id;
                            $payroll->is_user = $owner->is_user;
                            $payroll->payment_date = $file->payment_date;
                            $first_date = SatellitePaymentFile::select('start_date')->where('payment_date', $file->payment_date)->orderBy('start_date', 'ASC')->first();
                            $last_date = SatellitePaymentFile::select('end_date')->where('payment_date', $file->payment_date)->orderBy('end_date', 'DESC')->first();
                            $payroll->payment_range = $first_date->start_date." al ".$last_date->end_date;
                            $payroll->total = $amount;
                            $commission_percent = SatellitePaymentAccount::where('owner_id', $id)->where('payment_date', $payment_date)->orderBy('old_xc', 'DESC')->first();
                            $commission_percent = $commission_percent->old_xc;
                            $payroll->percent = $commission_percent;

                            $percent_gb = round(($amount * (100 - $commission_percent) / 100), 2);
                            $percent_studio = $amount - $percent_gb;
                            $payroll->percent_studio = $percent_studio;
                            $payroll->percent_gb = $percent_gb;

                            $trm_value = SatellitePaymentFile::select('trm')->where('payment_date', $file->payment_date)->first();
                            $payroll->trm = $trm_value->trm;

                            $payroll->percent_gb_pesos = round($percent_gb * $trm_value->trm);

                            $transaction = 0;
                            $retention = 0;

                            $old_payment = DB::connection('gbmedia')->table('sec_forma_pago')->select('fp_tipo_pago')->where('fp_fk_pro_id', $old_id)->where('fp_fecha_pago', $payment_date)
                                ->first();
                            if ($old_payment !== null)
                            {
                                $payment_method = $old_payment->fp_tipo_pago;
                            }
                            else
                            {
                                $payment_method = $owner->payment_method;
                            }

                            if ($payment_method == 1 || $payment_method == 2 || $payment_method == 3 || $payment_method == 5 || $payment_method == 6 || $payment_method == 8 ||
                                $payment_method == 9) {
                                $transaction = 3570;
                            }

                            if ($payment_method == 1 || $payment_method == 2 || $payment_method == 3) {
                                $retention = round($trm_value->trm * ($percent_studio * 4 / 100));
                            }

                            $payroll->transaction = $transaction;
                            $payroll->retention = $retention;

                            $payroll->payment = round($percent_studio * $trm_value->trm) - $transaction - $retention;

                            $owner_payment_info = SatelliteOwnerPaymentInfo::where('owner', $id)->get();

                            $payroll->payment_methods_id = $payment_method;
                            $payroll->holder = $owner_payment_info[0]->holder;
                            $payroll->bank = $owner_payment_info[0]->bank;
                            $payroll->bank_usa = $owner_payment_info[0]->bank_usa;
                            $payroll->document_type = $owner_payment_info[0]->document_type;
                            $payroll->document_number = $owner_payment_info[0]->document_number;
                            $payroll->account_type = $owner_payment_info[0]->account_type;
                            $payroll->account_number = $owner_payment_info[0]->account_number;
                            $payroll->city_id = $owner_payment_info[0]->city_id;
                            $payroll->address = $owner_payment_info[0]->address;
                            $payroll->phone = $owner_payment_info[0]->phone;
                            $payroll->country = $owner_payment_info[0]->country;
                            $payroll->created_by = Auth::user()->id;
                            $payroll->rut = SatelliteOwnerDocumentation::where('owner', $owner->id)->where('type', 1)->exists();
                            $last_pay = SatellitePaymentPayroll::where('owner_id', $owner->id)->where('payment_date','<' ,$file->payment_date)
                                ->orderBy('payment_date', 'DESC')->first();

                            if ($last_pay != null)
                            {
                                $first_time = 0;
                                if ($last_pay->payment_methods_id != $owner->payment_method){
                                    $first_time = 1;
                                }
                                if ($last_pay->holder != $owner_payment_info[0]->holder){
                                    $first_time = 1;
                                }
                                if ($last_pay->bank != $owner_payment_info[0]->bank){
                                    $first_time = 1;
                                }
                                if ($last_pay->account_number != $owner_payment_info[0]->account_number){
                                    $first_time = 1;
                                }
                                if ($last_pay->document_number != $owner_payment_info[0]->document_number){
                                    $first_time = 1;
                                }
                                $payroll->first_time = $first_time;
                            }
                            else{
                                $payroll->first_time = 1;
                            }
                            $payroll->save();

                            $paydeductions = SatellitePaymentPayDeduction::select('satellite_payment_paydeductions.*')
                                ->join('satellite_payment_deductions', 'satellite_payment_paydeductions.deduction_id', 'satellite_payment_deductions.id' )
                                ->where('satellite_payment_paydeductions.owner_id', $owner->id)
                                ->where('satellite_payment_paydeductions.payment_date', $payment_date)
                                ->orderBy('satellite_payment_deductions.deduction_to', 'ASC')->get();
                            $res_total = 0;
                            $res_percent = 0;
                            $res_payment = 0;
                            foreach ($paydeductions as $paydeduction)
                            {

                                $amount = $paydeduction->amount;
                                $deduction = SatellitePaymentDeduction::where('id', $paydeduction->deduction_id)->first();

                                if ($deduction->deduction_to == 0) {
                                    $res_total = $res_total + $amount;
                                }

                                if ($deduction->deduction_to == 1) {
                                    $res_percent = $res_percent + $amount;
                                }

                                if ($deduction->deduction_to == 2) {
                                    $res_payment = $res_payment + $amount;
                                }
                                $paydeduction->payroll_id = $payroll->id;
                                $paydeduction->save();

                                /*$deduction->amount = $deduction->amount - $amount;
                                $deduction->times_paid = $deduction->times_paid + 1;
                                $deduction->last_pay = $paydeduction->payment_date;
                                $deduction->save();*/
                            }

                            $commissions = SatellitePaymentCommission::where('owner_id', $owner->id)->where('payment_date', $payment_date)->orderBy('assign_to', 'ASC')->get();
                            $sum_total = 0;
                            $sum_percent = 0;
                            $sum_payment = 0;
                            foreach ($commissions as $commission)
                            {
                                $amount = $commission->amount;
                                if ($commission->assign_to == 0) {
                                    $sum_total = $sum_total + $amount;

                                }

                                if ($commission->assign_to == 1) {
                                    $sum_percent = $sum_percent + $amount;
                                }

                                if ($commission->assign_to == 2) {
                                    $sum_payment = $sum_payment + $amount;
                                }

                                $commission->payroll_id = $payroll->id;
                                $commission->save();
                            }

                            $total = $payroll->total + $sum_total - $res_total;
                            $percent_gb = round(($total * (100 - $commission_percent) / 100), 2);
                            $percent_studio = $total - $percent_gb + $sum_percent - $res_percent;

                            $retention = 0;

                            if ($payment_method == 1 || $payment_method == 2 || $payment_method == 3) {
                               $retention = round($trm_value->trm * ($percent_studio * 4 / 100));
                            }

                            $payment = round($percent_studio * $trm_value->trm) - $transaction - $retention;
                            $payroll->total = $total;
                            $payroll->percent_gb = $percent_gb;
                            $payroll->percent_studio = $percent_studio;
                            $payroll->retention = $retention;
                            $payroll->payment = $payment + $sum_payment - $res_payment;
                            $payroll->save();

                            SatellitePaymentAccount::where('owner_id', $owner->id)->where('payroll_id', null)
                                ->where('payment_date', '<=' ,$payment_date)->update(['payroll_id' => $payroll->id]);
                        }
                        else
                        {
                            echo "<br> no se encuentra el file";
                        }
                    }
                }
            }

            $msg = "Yeah";
            return response()->json($msg);
    }

    public function scriptOwnerPayroll2($id, $old_id)
    {
            $dates = DB::connection('gbmedia')->table('sec_acumulado')
                ->where('sec_ac_fk_prop_id', $old_id)
                //->where('sec_ac_fecha_pago', '>', '2020-12-28')
                ->distinct('sec_ac_fecha_pago')
                ->get();
            $value = 0;
            foreach ($dates as $date)
            {
                $payment_date = $date->sec_ac_fecha_pago;
                $amount = SatellitePaymentAccount::where('owner_id', $id)->where('payroll_id', null)->where('payment_date', '<=' ,$payment_date)->sum('amount');
                $acumulado = DB::connection('gbmedia')->table('sec_acumulado')->where('sec_ac_fk_prop_id', $old_id)->where('sec_ac_fecha_pago', $payment_date)->get();

                if ($amount >= 65 && $acumulado[0]->sigue_acumulando != 1 || ($acumulado[0]->sigue_acumulando == 3))
                    {
                        $payroll = new SatellitePaymentPayroll;
                        $owner = SatelliteOwner::find($id);

                        $file = SatellitePaymentFile::select('payment_date', 'trm')->where('payment_date', $payment_date)->first();

                        if ($file != null) {
                            $payroll->owner_id = $owner->id;
                            $payroll->is_user = $owner->is_user;
                            $payroll->payment_date = $file->payment_date;
                            $first_date = SatellitePaymentFile::select('start_date')->where('payment_date', $file->payment_date)->orderBy('start_date', 'ASC')->first();
                            $last_date = SatellitePaymentFile::select('end_date')->where('payment_date', $file->payment_date)->orderBy('end_date', 'DESC')->first();
                            $payroll->payment_range = $first_date->start_date." al ".$last_date->end_date;
                            $payroll->total = $amount;
                            $commission_percent = SatellitePaymentAccount::where('owner_id', $id)->where('payment_date', $payment_date)->orderBy('old_xc', 'DESC')->first();
                            if ($commission_percent !== null)
                            {
                                $commission_percent = $commission_percent->old_xc;
                            }
                            else
                            {
                                $commission_percent = $owner->commission_percent;
                            }

                            $payroll->percent = $commission_percent;

                            $percent_gb = round(($amount * (100 - $commission_percent) / 100), 2);
                            $percent_studio = $amount - $percent_gb;
                            $payroll->percent_studio = $percent_studio;
                            $payroll->percent_gb = $percent_gb;

                            $trm_value = SatellitePaymentFile::select('trm')->where('payment_date', $file->payment_date)->first();
                            $payroll->trm = $trm_value->trm;

                            $payroll->percent_gb_pesos = round($percent_gb * $trm_value->trm);

                            $transaction = 0;
                            $retention = 0;

                            $old_payment = DB::connection('gbmedia')->table('sec_forma_pago')->select('fp_tipo_pago')
                                ->where('fp_fk_pro_id', $old_id)->where('fp_fecha_pago', $payment_date)
                                ->first();
                            if ($old_payment !== null)
                            {
                                $payment_method = $old_payment->fp_tipo_pago;
                            }
                            else
                            {
                                $payment_method = $owner->payment_method;
                            }

                            if ($payment_method == 1 || $payment_method == 2 || $payment_method == 3 || $payment_method == 5 || $payment_method == 6 || $payment_method == 8 ||
                                $payment_method == 9) {
                                $transaction = 3570;
                            }

                            if ($payment_method == 1 || $payment_method == 2 || $payment_method == 3) {
                                $retention = round($trm_value->trm * ($percent_studio * 4 / 100));
                            }

                            $payroll->transaction = $transaction;
                            $payroll->retention = $retention;

                            $payroll->payment = round($percent_studio * $trm_value->trm) - $transaction - $retention;

                            $owner_payment_info = SatelliteOwnerPaymentInfo::where('owner', $id)->get();

                            $payroll->payment_methods_id = (is_numeric($payment_method) )? $payment_method : 1;
                            $payroll->holder = $owner_payment_info[0]->holder;
                            $payroll->bank = $owner_payment_info[0]->bank;
                            $payroll->bank_usa = $owner_payment_info[0]->bank_usa;
                            $payroll->document_type = $owner_payment_info[0]->document_type;
                            $payroll->document_number = $owner_payment_info[0]->document_number;
                            $payroll->account_type = $owner_payment_info[0]->account_type;
                            $payroll->account_number = $owner_payment_info[0]->account_number;
                            $payroll->city_id = $owner_payment_info[0]->city_id;
                            $payroll->address = $owner_payment_info[0]->address;
                            $payroll->phone = $owner_payment_info[0]->phone;
                            $payroll->country = $owner_payment_info[0]->country;
                            $payroll->created_by = Auth::user()->id;
                            $payroll->rut = SatelliteOwnerDocumentation::where('owner', $owner->id)->where('type', 1)->exists();
                            $last_pay = SatellitePaymentPayroll::where('owner_id', $owner->id)->where('payment_date','<' ,$file->payment_date)
                                ->orderBy('payment_date', 'DESC')->first();

                            if ($last_pay != null)
                            {
                                $first_time = 0;
                                if ($last_pay->payment_methods_id != $owner->payment_method){
                                    $first_time = 1;
                                }
                                if ($last_pay->holder != $owner_payment_info[0]->holder){
                                    $first_time = 1;
                                }
                                if ($last_pay->bank != $owner_payment_info[0]->bank){
                                    $first_time = 1;
                                }
                                if ($last_pay->account_number != $owner_payment_info[0]->account_number){
                                    $first_time = 1;
                                }
                                if ($last_pay->document_number != $owner_payment_info[0]->document_number){
                                    $first_time = 1;
                                }
                                $payroll->first_time = $first_time;
                            }
                            else{
                                $payroll->first_time = 1;
                            }
                            $payroll->save();

                            $paydeductions = SatellitePaymentPayDeduction::select('satellite_payment_paydeductions.*')
                                ->join('satellite_payment_deductions', 'satellite_payment_paydeductions.deduction_id', 'satellite_payment_deductions.id' )
                                ->where('satellite_payment_paydeductions.owner_id', $owner->id)
                                ->where('satellite_payment_paydeductions.payment_date', $payment_date)
                                ->orderBy('satellite_payment_deductions.deduction_to', 'ASC')->get();
                            $res_total = 0;
                            $res_percent = 0;
                            $res_payment = 0;
                            foreach ($paydeductions as $paydeduction)
                            {

                                $amount = $paydeduction->amount;
                                $deduction = SatellitePaymentDeduction::where('id', $paydeduction->deduction_id)->first();

                                if ($deduction->deduction_to == 0) {
                                    $res_total = $res_total + $amount;
                                }

                                if ($deduction->deduction_to == 1) {
                                    $res_percent = $res_percent + $amount;
                                }

                                if ($deduction->deduction_to == 2) {
                                    $res_payment = $res_payment + $amount;
                                }
                                $paydeduction->payroll_id = $payroll->id;
                                $paydeduction->save();

                                /*$deduction->amount = $deduction->amount - $amount;
                                $deduction->times_paid = $deduction->times_paid + 1;
                                $deduction->last_pay = $paydeduction->payment_date;
                                $deduction->save();*/
                            }

                            $commissions = SatellitePaymentCommission::where('owner_id', $owner->id)->where('payment_date', $payment_date)->orderBy('assign_to', 'ASC')->get();
                            $sum_total = 0;
                            $sum_percent = 0;
                            $sum_payment = 0;
                            foreach ($commissions as $commission)
                            {
                                $amount = $commission->amount;
                                if ($commission->assign_to == 0) {
                                    $sum_total = $sum_total + $amount;

                                }

                                if ($commission->assign_to == 1) {
                                    $sum_percent = $sum_percent + $amount;
                                }

                                if ($commission->assign_to == 2) {
                                    $sum_payment = $sum_payment + $amount;
                                }

                                $commission->payroll_id = $payroll->id;
                                $commission->save();
                            }

                            $total = $payroll->total + $sum_total - $res_total;
                            $percent_gb = round(($total * (100 - $commission_percent) / 100), 2);
                            $percent_studio = $total - $percent_gb + $sum_percent - $res_percent;

                            $retention = 0;

                            if ($payment_method == 1 || $payment_method == 2 || $payment_method == 3) {
                               $retention = round($trm_value->trm * ($percent_studio * 4 / 100));
                            }

                            $payment = round($percent_studio * $trm_value->trm) - $transaction - $retention;
                            $payroll->total = $total;
                            $payroll->percent_gb = $percent_gb;
                            $payroll->percent_studio = $percent_studio;
                            $payroll->retention = $retention;
                            $payroll->payment = $payment + $sum_payment - $res_payment;
                            $payroll->save();

                            SatellitePaymentAccount::where('owner_id', $owner->id)->where('payroll_id', null)
                                ->where('payment_date', '<=' ,$payment_date)->update(['payroll_id' => $payroll->id]);
                        }
                        else
                        {
                            echo "<br> no se encuentra el file";
                        }
                    }

            }

            $msg = "Yeah";
            return response()->json($msg);
    }

    public function scriptOwnerPayroll3($id, $old_id)
    {
        $dates = SatellitePaymentAccount::where('owner_id', $id)->where('payment_date', '>', '2020-12-28')->distinct('payment_date')->get();

        $value = 0;
        foreach ($dates as $date)
        {
            $payment_date = $date->payment_date;
            $amount = SatellitePaymentAccount::where('owner_id', $id)->where('payroll_id', null)->where('payment_date', '<=' ,$payment_date)->sum('amount');
            $acumulado = DB::connection('gbmedia')->table('sec_acumulado')->where('sec_ac_fk_prop_id', $old_id)->where('sec_ac_fecha_pago', $payment_date)->get();
            if ($acumulado === null)
            {
                continue;
            }

            if ($amount >= 1 || $acumulado[0]->sigue_acumulando == 3)
            {

                if ($acumulado[0]->sigue_acumulando != 1 || $acumulado[0]->sigue_acumulando == 3)
                {
                    $exists = SatellitePaymentPayroll::where('owner_id', $id)->where('payment_date', $payment_date)->exists();
                    if ($exists)
                    {
                        $payroll = SatellitePaymentPayroll::where('owner_id', $id)->where('payment_date', $payment_date)->first();
                    }
                    else
                    {
                        $payroll = new SatellitePaymentPayroll;
                    }

                    $owner = SatelliteOwner::find($id);

                    $file = SatellitePaymentFile::select('payment_date', 'trm')->where('payment_date', $payment_date)->first();

                    if ($file != null) {
                        $payroll->owner_id = $owner->id;
                        $payroll->is_user = $owner->is_user;
                        $payroll->payment_date = $file->payment_date;
                        $first_date = SatellitePaymentFile::select('start_date')->where('payment_date', $file->payment_date)->orderBy('start_date', 'ASC')->first();
                        $last_date = SatellitePaymentFile::select('end_date')->where('payment_date', $file->payment_date)->orderBy('end_date', 'DESC')->first();
                        $payroll->payment_range = $first_date->start_date." al ".$last_date->end_date;
                        $payroll->total = $amount;
                        $commission_percent = SatellitePaymentAccount::where('owner_id', $id)->where('payment_date', $payment_date)->orderBy('old_xc', 'DESC')->first();
                        $commission_percent = $commission_percent->old_xc;
                        $payroll->percent = $commission_percent;

                        $percent_gb = round(($amount * (100 - $commission_percent) / 100), 2);
                        $percent_studio = $amount - $percent_gb;
                        $payroll->percent_studio = $percent_studio;
                        $payroll->percent_gb = $percent_gb;

                        $trm_value = SatellitePaymentFile::select('trm')->where('payment_date', $file->payment_date)->first();
                        $payroll->trm = $trm_value->trm;

                        $payroll->percent_gb_pesos = round($percent_gb * $trm_value->trm);

                        $transaction = 0;
                        $retention = 0;

                        $old_payment = DB::connection('gbmedia')->table('sec_forma_pago')->select('fp_tipo_pago')->where('fp_fk_pro_id', $old_id)->where('fp_fecha_pago', $payment_date)
                            ->first();
                        if ($old_payment !== null)
                        {
                            $payment_method = $old_payment->fp_tipo_pago;
                        }
                        else
                        {
                            $payment_method = $owner->payment_method;
                        }

                        if ($payment_method == 1 || $payment_method == 2 || $payment_method == 3 || $payment_method == 5 || $payment_method == 6 || $payment_method == 8 ||
                            $payment_method == 9) {
                            $transaction = 3570;
                        }

                        if ($payment_method == 1 || $payment_method == 2 || $payment_method == 3) {
                            $retention = round($trm_value->trm * ($percent_studio * 4 / 100));
                        }

                        $payroll->transaction = $transaction;
                        $payroll->retention = $retention;

                        $payroll->payment = round($percent_studio * $trm_value->trm) - $transaction - $retention;

                        $owner_payment_info = SatelliteOwnerPaymentInfo::where('owner', $id)->get();

                        $payroll->payment_methods_id = $payment_method;
                        $payroll->holder = $owner_payment_info[0]->holder;
                        $payroll->bank = $owner_payment_info[0]->bank;
                        $payroll->bank_usa = $owner_payment_info[0]->bank_usa;
                        $payroll->document_type = $owner_payment_info[0]->document_type;
                        $payroll->document_number = $owner_payment_info[0]->document_number;
                        $payroll->account_type = $owner_payment_info[0]->account_type;
                        $payroll->account_number = $owner_payment_info[0]->account_number;
                        $payroll->city_id = $owner_payment_info[0]->city_id;
                        $payroll->address = $owner_payment_info[0]->address;
                        $payroll->phone = $owner_payment_info[0]->phone;
                        $payroll->country = $owner_payment_info[0]->country;
                        $payroll->created_by = Auth::user()->id;
                        $payroll->rut = SatelliteOwnerDocumentation::where('owner', $owner->id)->where('type', 1)->exists();
                        $last_pay = SatellitePaymentPayroll::where('owner_id', $owner->id)->where('payment_date','<' ,$file->payment_date)
                            ->orderBy('payment_date', 'DESC')->first();

                        if ($last_pay != null)
                        {
                            $first_time = 0;
                            if ($last_pay->payment_methods_id != $owner->payment_method){
                                $first_time = 1;
                            }
                            if ($last_pay->holder != $owner_payment_info[0]->holder){
                                $first_time = 1;
                            }
                            if ($last_pay->bank != $owner_payment_info[0]->bank){
                                $first_time = 1;
                            }
                            if ($last_pay->account_number != $owner_payment_info[0]->account_number){
                                $first_time = 1;
                            }
                            if ($last_pay->document_number != $owner_payment_info[0]->document_number){
                                $first_time = 1;
                            }
                            $payroll->first_time = $first_time;
                        }
                        else{
                            $payroll->first_time = 1;
                        }
                        $payroll->save();

                        $paydeductions = SatellitePaymentPayDeduction::select('satellite_payment_paydeductions.*')
                            ->join('satellite_payment_deductions', 'satellite_payment_paydeductions.deduction_id', 'satellite_payment_deductions.id' )
                            ->where('satellite_payment_paydeductions.owner_id', $owner->id)
                            ->where('satellite_payment_paydeductions.payment_date', $payment_date)
                            ->orderBy('satellite_payment_deductions.deduction_to', 'ASC')->get();
                        $res_total = 0;
                        $res_percent = 0;
                        $res_payment = 0;
                        foreach ($paydeductions as $paydeduction)
                        {

                            $amount = $paydeduction->amount;
                            $deduction = SatellitePaymentDeduction::where('id', $paydeduction->deduction_id)->first();

                            if ($deduction->deduction_to == 0) {
                                $res_total = $res_total + $amount;
                            }

                            if ($deduction->deduction_to == 1) {
                                $res_percent = $res_percent + $amount;
                            }

                            if ($deduction->deduction_to == 2) {
                                $res_payment = $res_payment + $amount;
                            }
                            $paydeduction->payroll_id = $payroll->id;
                            $paydeduction->save();

                            /*$deduction->amount = $deduction->amount - $amount;
                            $deduction->times_paid = $deduction->times_paid + 1;
                            $deduction->last_pay = $paydeduction->payment_date;
                            $deduction->save();*/
                        }

                        $commissions = SatellitePaymentCommission::where('owner_id', $owner->id)->where('payment_date', $payment_date)->orderBy('assign_to', 'ASC')->get();
                        $sum_total = 0;
                        $sum_percent = 0;
                        $sum_payment = 0;
                        foreach ($commissions as $commission)
                        {
                            $amount = $commission->amount;
                            if ($commission->assign_to == 0) {
                                $sum_total = $sum_total + $amount;

                            }

                            if ($commission->assign_to == 1) {
                                $sum_percent = $sum_percent + $amount;
                            }

                            if ($commission->assign_to == 2) {
                                $sum_payment = $sum_payment + $amount;
                            }

                            $commission->payroll_id = $payroll->id;
                            $commission->save();
                        }

                        $total = $payroll->total + $sum_total - $res_total;
                        $percent_gb = round(($total * (100 - $commission_percent) / 100), 2);
                        $percent_studio = $total - $percent_gb + $sum_percent - $res_percent;

                        $retention = 0;

                        if ($payment_method == 1 || $payment_method == 2 || $payment_method == 3) {
                            $retention = round($trm_value->trm * ($percent_studio * 4 / 100));
                        }

                        $payment = round($percent_studio * $trm_value->trm) - $transaction - $retention;
                        $payroll->total = $total;
                        $payroll->percent_gb = $percent_gb;
                        $payroll->percent_studio = $percent_studio;
                        $payroll->retention = $retention;
                        $payroll->payment = $payment + $sum_payment - $res_payment;
                        $payroll->save();

                        SatellitePaymentAccount::where('owner_id', $owner->id)->where('payroll_id', null)
                            ->where('payment_date', '<=' ,$payment_date)->update(['payroll_id' => $payroll->id]);
                    }
                    else
                    {
                        echo "<br> no se encuentra el file";
                    }
                }
            }
        }

        $msg = "Yeah";
        return response()->json($msg);
    }

    public function scriptOwnerDeductionTimesPaid()
    {
        $paydeductions = SatellitePaymentPayDeduction::where('payment_date', '>=', '2020-12-28')->where('last_pay', '')->get();
        foreach ($paydeductions as $paydeduction)
        {
            $deduction = SatellitePaymentDeduction::where('id', $paydeduction->deduction_id)->first();
            $deduction->times_paid = $deduction->times_paid + 1;
            $deduction->last_pay = $paydeduction->payment_date;
            $deduction->save();
        }

        $msg = "Yeah";
        return response()->json($msg);
    }

    public function scriptOwnerDeductions()
    {
        $deductions = SatellitePaymentDeduction::all();
        foreach ($deductions as $deduction)
        {
            $sum = SatellitePaymentPayDeduction::where('deduction_id', $deduction->id)->sum('amount');
            $deduction->amount = $deduction->total - $sum;
            $deduction->save();
            echo "<br>". $deduction->amount." == ded_id == ".$deduction->id." == owner_id == ".$deduction->owner_id;
        }

        $msg = "Yeah";
        return response()->json($msg);
    }

    public function scriptOwnerCommissionTo()
    {
        $percents = DB::connection('gbmedia')->table('sec_propietario')->where('pro_comisiona_estado', 1)->get();
        foreach ($percents as $value)
        {
            $old_id = $value->pro_id;

            echo "<br> ======". $old_id;

            echo "<br>1: ". $value->pro_comisiona_para;
            echo "<br>2: ". $value->pro_comisiona_para1;
            echo "<br>3: ". $value->pro_comisiona_para3;
            echo "<br>4: ". $value->pro_comisiona_para4;

            if ($value->pro_comisiona_para > 0)
            {
                $commission = new SatelliteOwnerCommissionRelation;
                $owner_giver = SatelliteOwner::where('old_id', $old_id)->first();
                $commission->owner_giver = $owner_giver->id;
                $owner_receiver = SatelliteOwner::where('old_id', $value->pro_comisiona_para)->first();
                $commission->owner_receiver = $owner_receiver->id;
                $commission->percent = $value->xciento_comision;
                //se puso commission type en 3 porque camsoda no se va a comisionar mas por ahora
                //$type = ($commission->type == 1)? "Todas las paginas" : (($commission->type == 2)? "Solo esta pagina" : "Todas excepto esta");
                $commission_type = 3;
                $page = 4;
                if ($value->pagina_comision != "")
                {
                    $page = ucwords($value->pagina_comision);
                    $setting_page = SettingPage::where('name', $page)->first();
                    $page = $setting_page->id;
                    $commission_type = 2;
                }

                $commission->type = $commission_type;
                $commission->page = $page;
                $commission->save();
            }
            if ($value->pro_comisiona_para1 > 0)
            {
                $commission = new SatelliteOwnerCommissionRelation;
                $owner_giver = SatelliteOwner::where('old_id', $old_id)->first();
                $commission->owner_giver = $owner_giver->id;
                $owner_receiver = SatelliteOwner::where('old_id', $value->pro_comisiona_para1)->first();
                $commission->owner_receiver = $owner_receiver->id;
                $commission->percent = $value->xciento_comision1;
                //se puso commission type en 3 porque camsoda no se va a comisionar mas por ahora
                //$type = ($commission->type == 1)? "Todas las paginas" : (($commission->type == 2)? "Solo esta pagina" : "Todas excepto esta");
                $commission_type = 3;
                $page = 4;
                if ($value->pagina_comision1 != "")
                {
                    $page = ucwords($value->pagina_comision1);
                    $setting_page = SettingPage::where('name', $page)->first();
                    $page = $setting_page->id;
                    $commission_type = 2;
                }

                $commission->type = $commission_type;
                $commission->page = $page;
                $commission->save();
            }
            if ($value->pro_comisiona_para3 > 0)
            {
                $commission = new SatelliteOwnerCommissionRelation;
                $owner_giver = SatelliteOwner::where('old_id', $old_id)->first();
                $commission->owner_giver = $owner_giver->id;
                $owner_receiver = SatelliteOwner::where('old_id', $value->pro_comisiona_para3)->first();
                $commission->owner_receiver = $owner_receiver->id;
                $commission->percent = $value->xciento_comision3;
                //se puso commission type en 3 porque camsoda no se va a comisionar mas por ahora
                //$type = ($commission->type == 1)? "Todas las paginas" : (($commission->type == 2)? "Solo esta pagina" : "Todas excepto esta");
                $commission_type = 3;
                $page = 4;
                if ($value->pagina_comision3 != "")
                {
                    $page = ucwords($value->pagina_comision3);
                    $setting_page = SettingPage::where('name', $page)->first();
                    $page = $setting_page->id;
                }

                $commission->type = $commission_type;
                $commission->page = $page;
                $commission->save();
            }
            if ($value->pro_comisiona_para4 > 0)
            {
                $commission = new SatelliteOwnerCommissionRelation;
                $owner_giver = SatelliteOwner::where('old_id', $old_id)->first();
                $commission->owner_giver = $owner_giver->id;
                $owner_receiver = SatelliteOwner::where('old_id', $value->pro_comisiona_para4)->first();
                $commission->owner_receiver = $owner_receiver->id;
                $commission->percent = $value->xciento_comision4;
                //se puso commission type en 3 porque camsoda no se va a comisionar mas por ahora
                //$type = ($commission->type == 1)? "Todas las paginas" : (($commission->type == 2)? "Solo esta pagina" : "Todas excepto esta");
                $commission_type = 3;
                $page = 4;
                if ($value->pagina_comision4 != "")
                {
                    $page = ucwords($value->pagina_comision4);
                    $setting_page = SettingPage::where('name', $page)->first();
                    $page = $setting_page->id;
                }

                $commission->type = $commission_type;
                $commission->page = $page;
                $commission->save();
            }
        }

        $msg = "Yeah";
        return response()->json($msg);
    }

    public function scriptOwnerRut()
    {
        $files = DB::connection('gbmedia')->table('sec_propietario')->where('rut_archivo', '!=', '')->get();
        foreach ($files as $value)
        {
            $old_id = $value->pro_id;
            echo "<br>". $old_id;
            $owner = SatelliteOwner::where('old_id', $old_id)->first();

            $owner_documentation = new SatelliteOwnerDocumentation;
            $owner_documentation->owner = $owner->id;
            $owner_documentation->type = 1;
            $owner_documentation->file = $value->rut_archivo;
            $owner_documentation->save();

        }

        $msg = "Yeah";
        return response()->json($msg);
    }

    public function scriptOwnerFileChamberCommerce()
    {
        $files = DB::connection('gbmedia')->table('sec_camara_comercio')->get();
        foreach ($files as $value)
        {
            $old_id = $value->scc_pro_id;
            echo "<br>". $old_id;
            $owner = SatelliteOwner::where('old_id', $old_id)->first();

            $owner_documentation = new SatelliteOwnerDocumentation;
            $owner_documentation->owner = $owner->id;
            $owner_documentation->type = 2;
            $owner_documentation->file = $value->scc_file;
            $owner_documentation->save();

        }

        $msg = "Yeah";
        return response()->json($msg);
    }

    public function scriptOwnerFileBankCertification()
    {
        $files = DB::connection('gbmedia')->table('sec_certificacion_bancaria')->get();
        foreach ($files as $value)
        {
            $old_id = $value->scb_pro_id;
            echo "<br>". $old_id;
            $owner = SatelliteOwner::where('old_id', $old_id)->first();

            $owner_documentation = new SatelliteOwnerDocumentation;
            $owner_documentation->owner = $owner->id;
            $owner_documentation->type = 4;
            $owner_documentation->file = $value->scb_file;
            $owner_documentation->save();

        }

        $msg = "Yeah";
        return response()->json($msg);
    }

    public function scriptOwnerFileShareholderStructure()
    {
        $files = DB::connection('gbmedia')->table('sec_composicion_accionaria')->get();
        foreach ($files as $value)
        {
            $old_id = $value->sca_pro_id;
            echo "<br>". $old_id;
            $owner = SatelliteOwner::where('old_id', $old_id)->first();

            $owner_documentation = new SatelliteOwnerDocumentation;
            $owner_documentation->owner = $owner->id;
            $owner_documentation->type = 3;
            $owner_documentation->file = $value->sca_file;
            $owner_documentation->save();

        }

        $msg = "Yeah";
        return response()->json($msg);
    }

    public function scriptUsers()
    {
        $files = DB::connection('gbmedia')->table('sec_usuarios')->where('id', '>', '8613')->get();
        foreach ($files as $value)
        {
            $old_id = $value->id;
            $user = new SatelliteUser;
            $user->old_id = $value->id;
            $user->first_name = $value->first_name;
            $user->second_name = $value->second_name;
            $user->last_name = $value->first_lastname;
            $user->second_last_name = $value->second_lastname;
            $user->document_type = $value->document_type;
            $user->document_number = $value->document_number;
            $country = GlobalCountry::where('code', $value->document_country)->first();
            $user->country_id = $country->id;
            $user->birth_date = ($value->birth_date == "0000-00-00")? null : $value->birth_date;

            $user_search = User::select('id')->where('old_user_id', $value->created_by)->first();
            if ($user_search != null){
                $user->created_by = $user_search->id;
            }


            $user_search = User::select('id')->where('old_user_id', $value->updated_by)->first();
            if ($user_search != null){
                $user->modified_by = $user_search->id;
            }
            $user->status = 1;
            $user->save();

        }

        $msg = "Yeah";
        return response()->json($msg);
    }

    public function scriptUsersFiles()
    {
        $files = DB::connection('gbmedia')->table('sec_usuarios_imagenes')->where('id', '>', '8613')->get();
        foreach ($files as $value)
        {
            $image = new SatelliteUsersImage;
            $image->old_id = $value->id;
            $image->image = $value->image;
            $old_id = $value->user_id;
            $user = SatelliteUser::where('old_id', $old_id)->first();
            $image->satellite_user_id = ($user == null)? 0 : $user->id;
            if ($value->type == "front_document")
                $image->type = 1;
            if ($value->type == "back_document")
                $image->type = 2;
            if ($value->type == "holding_document")
                $image->type = 3;
            if ($value->type == "profile_image")
                $image->type = 4;

            $image->save();

        }

        $msg = "Yeah";
        return response()->json($msg);
    }

    public function scriptContract()
    {
        $files = DB::connection('gbmedia')->table('sec_contratos')->get();
        foreach ($files as $value)
        {
            $contract = new SatelliteContract;
            $contract->studio_name = $value->sc_name_studio;
            $contract->company_type = ($value->sc_tipo_repres == 1)? "Persona Natural" : "Empresa";
            $contract->holder = $value->sc_representante;
            $contract->card_id = $value->sc_identificacion;
            $contract->company = $value->sc_empresa;
            $contract->nit = $value->sc_nit;

            $contract->address = $value->sc_address;
            $contract->city = $value->sc_city;
            $contract->department = $value->sc_departamento;
            $contract->phone = $value->sc_tel;
            $contract->email = $value->sc_email;
            $contract->percent = $value->sc_xc;
            $contract->payment_method = ($value->sc_tipo_trasnferencia == 1)? "Bancaria" : (($value->sc_tipo_trasnferencia == 2) ? "Efecty" : "Paxum");
            $contract->clause = $value->sc_incumplimiento;
            $contract->years = $value->sc_duracion;
            $contract->increase = $value->sc_cont;
            $contract->from = $value->sc_from;
            $contract->from_name = "Tenant No Created";
            $contract->save();
        }
    }

    public function modifiedByAccount()
    {
        $min_id = 5628;
        $max_id = 53024;
        $accounts = SatelliteAccount::whereBetween('id', [$min_id, $max_id])->get();
        try {
            DB::beginTransaction();
            foreach ($accounts as $value)
            {
                $modified_by = $value->modified_by;
                $user = User::select('id')->where('old_user_id', $modified_by)->first();
                if ($user == null){
                    echo "<br>".$value->id;
                    continue;
                }
                $value->modified_by = $user->id;
                $value->save();
            }

            DB::Commit();
            return response()->json(['success' => true]);
        } catch (Exception $e) {
            DB::rollback();
            echo "ha ocurrido un error";
        }
    }

    public function scriptStatusAccounts()
    {
        $min_id = 3;
        $max_id = 53986;

        try {
            DB::beginTransaction();
            $accounts = DB::connection('gbmedia')->table('sec')->select('sec_id', 'fk_sec_estado_id')->whereBetween('sec_id', [$min_id, $max_id])->where('fk_sec_estado_id', 0)
                ->get();
            foreach ($accounts as $value)
            {
                $sec_id = $value->sec_id;
                $status = $value->fk_sec_estado_id;
                if ($status == 0)
                {
                    $status = 1;
                }
                elseif ($status == 1)
                {
                    $status = 2;
                }

                $account = SatelliteAccount::where('acc_old_id', $sec_id)->first();
                if ($account != null)
                {
                    $account->status_id = $status;
                    $account->save();
                }
            }
            echo "termina";
            DB::Commit();
            return response()->json(['success' => true]);
        } catch (Exception $e) {
            DB::rollback();
            echo "ha ocurrido un error";
        }
    }

    public function pageTemplatesExecute()
    {
        if (!Schema::hasColumn('satellite_templates_for_emails', 'old_template_id'))
        {
            Schema::table('satellite_templates_for_emails', function (Blueprint $table) {
                $table->string('old_template_id')->nullable();
            });
        }

        $pages = [
            7  => 1,
            8  => 2,
            9  => 3,
            10  => 4,
            12  => 5,
            13  => 6,
            14  => 7,
            15  => 8,
            17  => 9,
            25  => 12,
            26  => 13,
            27  => 20,
            28  => 14,
            29  => 15,
            30  => 16,
            31  => 17,
            32  => 18,
        ];

        $min_id = 1;
        $max_id = 200;

        $templates = DB::connection('gbmedia')->table('page_has_templates')->whereBetween('id', [$min_id, $max_id])->where('t_id', 2)->get();

        try {
            DB::beginTransaction();

            foreach ($templates AS $template) {
                $subject = $template->t_title;
                $page_id = $pages[$template->p_id];
                $created_at = $template->t_updated_at;

                $body = $template->t_body;
                $body = str_replace("{{nombre_completo}}", "{{full_name}}", $body);
                $body = str_replace("{{email}}", "{{access}}", $body);
                $body = str_replace("{{clave}}", "{{password}}", $body);

                $created_template = SatelliteTemplatesForEmail::firstOrCreate(
                    [
                        'old_template_id' => $template->id
                    ],
                    [
                        'template_page_id' => $page_id,
                        'subject' => $subject,
                        'old_template_id' => $template->id,
                        'created_at' => $created_at,
                        'updated_at' => $created_at,
                    ]
                );

                $created_template->template_page_id = $page_id;
                $created_template->subject = $subject;
                $created_template->body = $body;
                $created_template->user_id = 473;
                $created_template->template_type_id = 2;
                $created_template->created_at = $created_at;
                $created_template->updated_at = $created_at;
                $created_template->old_template_id = $template->id;
                $created_template->save();
            }

            DB::commit();

            return response()->json(['success' => true]);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json(['success' => false]);
        }
    }

    public function sendPendingEmailsExecute()
    {
        $accounts = SatelliteAccount::where('acc_old_id', NULL)->where('email_sent', 0)->where('first_name', 'NOT LIKE', '..')->get();
        $i = 0;

        try {
            DB::beginTransaction();

            foreach ($accounts AS $account) {
//                if($i == 5) { break; }

                $page_field = SatelliteTemplatesPagesField::where('id', $account->page_id)->where('template_type_id', 1)->first();

                $send_email = true;
                $email_send_status = 0;

                if ($page_field->full_name == 1 && ($account->first_name == "" || $account->last_name == "")) {
                    $send_email = false;
                    $email_send_status = 4;
                }

                if ($page_field->access == 1 && $account->access == "") {
                    $send_email = false;
                    $email_send_status = 4;
                }

                if ($page_field->password == 1 && $account->password == "") {
                    $send_email = false;
                    $email_send_status = 4;
                }

                if ($page_field->template_page->count() <= 0) {
                    $send_email = false;
                    $email_send_status = 3;
                }

                $owner_email = '';

                if($send_email) {
                    $owner = SatelliteOwner::select('email')->where('id', $account->owner_id)->first();
                    if(is_null($owner)) { // No owner
                        $email_send_status = 2;
                    } else {
                        $owner_email = $owner->email;

                        $full_name = $account->first_name." ".$account->second_name." ".$account->last_name." ".$account->second_last_name;
                        $mail['subject'] = $page_field->template_page[0]->subject;
                        $mail['pagina'] = $page_field->name;

                        $body = $page_field->template_page[0]->body;
                        $body = str_replace("{{email}}", $account->email, $body);
                        $body = str_replace("{{nick}}", $account->nick, $body);
                        $body = str_replace("{{full_name}}", $full_name, $body);
                        $body = str_replace("{{access}}", $account->access, $body);
                        $body = str_replace("{{password}}", $account->password, $body);
                        $mail['body'] = $body;
                        $sent = Mail::to($owner->email)->send(new CreatedAccount($mail));
                        //$sent = Mail::to("romangbmediagroup@gmail.com")->send(new CreatedAccount($mail));
                        //$sent = Mail::to("manuelgbmediagroup@gmail.com")->send(new CreatedAccount($mail));

                        $edit_account = SatelliteAccount::where('id', $account->id)->first();
                        $edit_account->email_sent = 1;
                        $edit_account->save();

                        dump("ID: $account->id | EMAIL SENT TO: $owner_email");
                    }
                }

                $i++;

                dump("------------------------------");
            }

            dump("DONE");

            DB::commit();

            return response()->json(['success' => true]);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json(['success' => false]);
        }
    }

    public function scriptSatelliteLogs()
    {
        $msg = "";
        $max_id = 55001;
        $min_id = 57000;

        $logs = DB::connection('gbmedia')->table('sec_historial')->whereBetween('sec_hist_id', [$max_id, $min_id])->get();
        echo $max_id." ".$min_id;
        foreach ($logs as $log)
        {
           $account = SatelliteAccount::where('acc_old_id', $log->fk_sec_id)->first();
           $description = $log->sec_hist_descrip;
           $explode = explode(':', $description);

           $replace = str_replace('Se modificÃ³', '', $explode[0]);
           if (is_null($replace)){
               continue;
           }
           if (isset($explode[1])){
               $change = explode('por', $explode[1]);
           }else{
               continue;
           }

           $xplode_replace = explode(' ', trim($replace));

           if (count($xplode_replace) > 3){
               $type1 = $xplode_replace[1];
               $type2 = $xplode_replace[2];
               $type = $type1." ".$type2;
           }else{
               $type = $xplode_replace[1];
           }

            if (count($change) >= 2){
                $previous = $change[0];
                $now = $change[1];
            }else{
                $previous = NULL;
                $now = $change[0];
            }

           $user = $log->sec_hist_modificado_por;
           $u = explode(' ', $user);
           $first = $u[0];
           $last = $u[1];

           if(!is_null($account)){
              $account_id  = $account->id;
           } else{
               continue;
           }

            $new_user = User::where('first_name', $first)->where('last_name', $last)->first();
            if(!is_null($new_user)){
                $new_user_id =  $new_user->id;
            }else{
                continue;
            }

            $my_date = trim($log->sec_hist_fecha);
            $log = new SatelliteAccountLog();
            $log->account_id = $account_id;
            $log->type = $type;
            $log->action = 'modificado';
            $log->previous = $previous;
            $log->now = $now;
            $log->created_by = $new_user_id;
            $log->created_at = "$my_date 00:00:01";
            $log->save();

            $msg = "Yeah";
        }

        return response()->json($msg);
    }

    public function scriptCreateAllCommission()
    {
        $payment_date = '2021-01-18';
        $receivers = SatelliteOwnerCommissionRelation::select('owner_receiver')->distinct('owner_receiver')->get();
        $total_rows = count($receivers);
        $count = 0;
        foreach ($receivers as $receiver) {
            $givers = SatelliteOwnerCommissionRelation::select('owner_giver')->where('owner_receiver', $receiver->owner_receiver)->distinct('owner_giver')->get();
            foreach ($givers as $giver){
                echo "<br>";
                echo "<br> ===";
                echo "<br>". $receiver->owner_receiver;
                echo "<br>". $giver->owner_giver;
                $this->createCommisionForReceiver($receiver->owner_receiver, $giver->owner_giver, $payment_date);
            }
            $count++;
            $verified_commissions["percent"] = ($count * 100 ) / ($total_rows);
            //event(new PaymentCommission($verified_commissions));
        }

        if ($count == 0)
        {
            $verified_commissions["percent"] = 100;
            //event(new PaymentCommission($verified_commissions));
        }

        return response()->json(['success' => true]);
    }

    public function setBDD()
    {
        $studios = [
            1 => [
                'owner_id' => 4,
                'studio_name' => 'trend',
            ],
            2 => [
                'owner_id' => 4,
                'studio_name' => 'gbmediag_admin',
            ],
            2 => [
                'owner_id' => 4,
                'studio_name' => 'agatha',
            ],
        ];

        foreach ($studios as $studio)
        {
            $database = $studio['studio_name'];
            echo "<br> ".$database. " ================================";
            $configDb = [
                'driver'    => 'mysql',
                'host'      => env('STUDIOS_HOST'),
                'port'      => env('STUDIOS_PORT'),
                'database'  => $database,
                'username'  => env('STUDIOS_USERNAME'),
                'password'  => env('STUDIOS_PASSWORD'),
            ];

            config(['database.connections.studios.database' => $database]);
            $connection = DB::connection("studios");

            $users = $connection->table('usuario')->select('nombre', 'apellidos')->skip(30)->take(10)->get();
            var_dump($users);
        }

    }

    public function scriptSecPendientesStudios()
    {
        $this->setBDD();
        /*$studios = [
            1 => [
                'owner_id' => 4,
                'studio_name' => 'trend',
            ],
            2 => [
                'owner_id' => 4,
                'studio_name' => 'trend',
            ],
        ];*/

        $payment_date = '2021-01-25';

        /*foreach ($studios AS $studio) {
            $users = [];

            config(['database.connections.studios.database' => $studio['studio_name']]);
            $database = config('database.connections.studios.database');

            $connection = DB::connection('studios');
            $users = $connection->table('usuario')->get();

            dump($database);
            dump($users);
        }*/

        /*$conn_to = [
            'owner_id' => 1086,
            'database' => 'shine',
        ];

        $owner_id = $conn_to['owner_id'];
        $payment_date = '2021-01-25';
        $payment_accounts = SatellitePaymentAccount::where('payment_date', $payment_date)->where('owner_id', $owner_id)->get();

        config(['database.connections.studios.database' => $conn_to['database']]);
        $database = config('database.connections.studios.database');*/

        //$studio = DB::connection('trend');
        /*$studio = DB::connection('studios');

        foreach ($payment_accounts as $payment_account)
        {
            $pagina = strtolower($payment_account->page->name);

            if($pagina == 'xlovecam') {
                $file = SatellitePaymentFile::where('id', $payment_account->file_id)->first();
                $description = "$file->start_date al $file->end_date";
            } else {
                $description = $payment_account->description;
            }

            $propietario = $studio->table('sec')->where('nick', $payment_account->nick)->where('pagina', $pagina)->first();
            $sap_fk_pro_id = ($propietario !=  null)? $propietario->fk_pro_id : 0;

            $studio->table('sec_asig_pendiente')->insert([
                'sap_fk_pro_id' => $sap_fk_pro_id,
                'sap_pagina' => $pagina,
                'sap_tipo' => '',
                'sap_fk_est_id' => $payment_account->id,
                'sap_nick_arch' => $payment_account->nick,
                'sap_descrip2' => $description,
                'sap_valor' => $payment_account->amount,
                'sap_fk_s_arc_id' => $payment_account->file_id,
                'sap_xciento_predeterm' => 90,
                'sap_fecha_bono' => '',
                'sap_descrip_bono' => '',
                'sap_week_bono' => '',
            ]);
        }*/

    }

    public function scriptApiChaturbate()
    {
        $msg = "nothing!";
        $min_id = 1;
        $max_id = 100;

        $apis = DB::connection('gbmedia')->table('sec_api_chaturbate')->whereBetween('sach_id', [$min_id, $max_id])->get();
        echo $min_id." ".$max_id;

        foreach($apis as $api)
        {
            SatelliteApi::create([
                'user' => $api->sach_propietario,
                'access_token' => $api->sach_token,
                'type' => 2,
            ]);

            $msg = "done!";
        }

        return response()->json($msg);
    }

    public function scriptSetModelInactive()
    {
        $msg = "nothing!";
        $min_id = 1;
        $max_id = 50;

        $models = User::where('setting_role_id', 14)->where('status', 0)->get();
        foreach($models as $model)
        {
            $owner = SatelliteOwner::where('user_id', $model->id)->where('is_user', 1)->update(['status' => 3]);
            echo "<br>".$owner;
        }

        return response()->json($msg);
    }

    public function scriptRollbackPayroll()
    {
        $payment_date = '2021-01-25';
        $payrolls = SatellitePaymentPayroll::where('payment_date', $payment_date)->get();

        foreach ($payrolls as $payroll)
        {
            SatellitePaymentAccount::where("payroll_id", $payroll->id)->update(["payroll_id" => null]);
            SatellitePaymentPayroll::where('id', $payroll->id)->delete();
        }
    }
}
