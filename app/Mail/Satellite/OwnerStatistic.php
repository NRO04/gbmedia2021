<?php

namespace App\Mail\Satellite;

use App\Exports\Satellite\PayrollStatistic;
use App\Exports\UsersExport;
use App\Models\Satellite\SatellitePaymentAccount;
use App\Models\Satellite\SatellitePaymentCommission;
use App\Models\Satellite\SatellitePaymentDeduction;
use App\Models\Satellite\SatellitePaymentPayDeduction;
use App\Models\Satellite\SatelliteTemplateStatistic;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\File;
use Maatwebsite\Excel\Facades\Excel;

class OwnerStatistic extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $mail;
    public $payroll;
    public $payroll_id;
    public $owner_id;
    public $payment_date;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($mail, $payroll_id ,$payroll, $owner_id, $payment_date)
    {
        $mail = SatelliteTemplateStatistic::first();
        if(file_exists( public_path()."storage/GB/logo/logo.png" )){
            $mail["logo"] = "<img src='".asset('storage/GB/logo/logo.png')."' style='height: 40px'>";
        }
        else{
            $mail["logo"] = "<h1 style='color: #2553FF'>Ejemplo</h1>";
        }
        $this->mail = $mail;

        $this->payroll_id = $payroll_id;
        $this->payroll = $payroll;
        $this->owner_id = $owner_id;
        $this->payment_date = $payment_date;

    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $accounts_send[0] = [];
        $payroll_accounts = SatellitePaymentAccount::where('payroll_id', $this->payroll_id)->get();

        foreach ($payroll_accounts as $key => $payroll_account)
        {
            $accounts_send[$key]["payment_date"] = $payroll_account->payment_date;
            $accounts_send[$key]["page"] = $payroll_account->page->name;
            $accounts_send[$key]["nick"] = "vacio";
            $accounts_send[$key]["amount"] = $payroll_account->amount;
            $accounts_send[$key]["description"] = $payroll_account->description;
        }

        $commission_send[0] = [];
        $commissions = SatellitePaymentCommission::where('payroll_id', $this->payroll_id)->get();
        foreach ($commissions as $key => $commission)
        {
            $commission_send[$key]["amount"] = $commission->amount;
            $commission_send[$key]["assign_to"] = ($commission->assign_to == 2)? "Pesos" : "Dolares";
            $commission_send[$key]["description"] = $commission->description;
        }

        $deduction_send[0] = [];
        $deductions = SatellitePaymentDeduction::where([
                ['owner_id', $this->owner_id],
                ['payment_date', null]
            ])->orWhere([
                ['owner_id', $this->owner_id],
                ['payment_date', '<=' , $this->payment_date ],
                ['finished_date', '>=' , $this->payment_date ],
                ['status', 1],
            ])->orWhere([
                ['owner_id', $this->owner_id],
                ['payment_date', '<=' , $this->payment_date ],
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

        $excel['payroll'] = $this->payroll['payroll'];
        $excel['accounts'] = $accounts_send;
        $excel['commissions'] = $commission_send;
        $excel['deductions'] = $deduction_send;

        Excel::store(new PayrollStatistic($excel), 'statistics/Pago.xlsx');

        return $this->markdown('emails.satellite.statistics')
            ->attach(storage_path('statistics/Pago.xlsx'));

    }
}
