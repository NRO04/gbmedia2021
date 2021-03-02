<?php

namespace App\Imports\Satellite\Payment;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;

use Auth;
use App\Models\Satellite\SatelliteAccount;
use App\Models\Satellite\SatellitePaymentAccount;
use App\Events\UploadedPayment;

class PaymentCSV implements ToCollection, WithCustomCsvSettings
{
    protected $page;
    protected $payment_file;

	public function __construct($page, $payment_file)
    {
        $this->page = $page;
        $this->payment_file = $payment_file;
    }

    public function collection(Collection $collection)
    {
        /*dd($collection);*/
        $total_rows = count($collection);
        $payment_date = $this->payment_file->payment_date;
        $file_id = $this->payment_file->id;
        $setting_page_id = $this->page->setting_page_id;
        $description = $this->payment_file->start_date." al ".$this->payment_file->end_date;
        $created_by = Auth::user()->id;

        $file_upload["file"] = $this->page['id'] - 1;

        for ($i=0; $i < $total_rows; $i++) { 
            
            $row = $collection[$i][0];
            $row = explode(",", $row);
            $nick  = trim($row[$this->page->cell_nick]);
            $nick = str_replace('"', "", $nick);
            
            $amount = str_replace("$", "", $row[$this->page->cell_value]);
            $amount  = trim($amount);
            $amount = str_replace(",", "", $amount);
            $amount = str_replace('"', "", $amount);

            //streamate studio
            if ($this->page['id'] == 3) {
                $amount   = (isset($row[4])) ? $amount . $row[4] : $amount;
                $amount  = trim($amount);
                $amount = str_replace('"', "", $amount);
            }

            if ($nick == "" && $amount == "" || $amount == 0) {
                $file_upload["percent"] = ($i * 100 ) / ($total_rows - 1);
                $file_upload["completed"] = ($i == $total_rows - 1)? 1 : 0;
                $file_upload["total_rows"] = $total_rows;
                $file_upload["i"] = $i;
                event(new UploadedPayment($file_upload));
                continue;
            }

            $owner_id = null;
            $account_id = null;

            //streamate studio
            if ($this->page['id'] == 3) {
                $account = SatelliteAccount::where('page_id', $setting_page_id)->where('access', $nick)->get();
            }
            else{
                $account = SatelliteAccount::where('page_id', $setting_page_id)->where('nick', $nick)->get();
            }
            
            if (count($account) > 0) {
                $owner_id = $account[0]->owner_id;
                $account_id = $account[0]->id;
            }

            $payment_account = new SatellitePaymentAccount([
                'owner_id' => $owner_id,
                'page_id' => $setting_page_id,
                'account_id' => $account_id,
                'file_id' => $file_id,
                'payment_date' => $payment_date,
                'amount' => $amount,
                'nick' => $nick,
                'description' => $description,
                'created_by' => $created_by,
            ]);

            $payment_account->save();
            
            $file_upload["percent"] = ($i * 100 ) / ($total_rows - 1);
            $file_upload["completed"] = ($i == $total_rows - 1)? 1 : 0;
            $file_upload["total_rows"] = $total_rows;
            $file_upload["i"] = $i;
            event(new UploadedPayment($file_upload));
        }
    }

    public function getCsvSettings(): array
    {
        return [
            'delimiter' => ",",
        ];
    }
}
