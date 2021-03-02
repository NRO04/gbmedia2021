<?php

namespace App\Imports\Satellite\Payment;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;

use Auth;
use App\Models\Satellite\SatelliteAccount;
use App\Models\Satellite\SatellitePaymentAccount;
use App\Events\UploadedPayment;

class PaymentExcel implements ToCollection, WithCalculatedFormulas
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
    	$total_rows = count($collection);
    	$payment_date = $this->payment_file->payment_date;
    	$file_id = $this->payment_file->id;
        $setting_page_id = $this->page->setting_page_id;
        $description = $this->payment_file->start_date." al ".$this->payment_file->end_date;
        $created_by = Auth::user()->id;

        $file_upload["file"] = $this->page['id'] - 1;

    	for ($i=0; $i < $total_rows; $i++) {

    		$row = $collection[$i];
            if ($this->page['id'] == 7 || $this->page['id'] == 6 || $this->page['id'] == 13)
            {
                $nick = $row[$this->page->cell_nick];
            }
        else
            {
                $nick = preg_replace('([^A-Za-z0-9_.-])', '', $row[$this->page->cell_nick]);
            }

    		/*dd($nick);*/
	    	$amount = str_replace("$", "", $row[$this->page->cell_value]);
	    	$amount = str_replace("€", "", $amount);
	        $amount = str_replace(",", "", $amount);
	        $amount  = trim($amount);
	        $owner_id = null;
	        $account_id = null;

	        if ($nick == "" && $amount == "" || $amount == 0) {
	        	$file_upload["percent"] = ($i * 100 ) / ($total_rows - 1);
		        $file_upload["completed"] = ($i == $total_rows - 1)? 1 : 0;
		        $file_upload["total_rows"] = $total_rows;
		        $file_upload["i"] = $i;
		        event(new UploadedPayment($file_upload));
                continue;
            }

	        $account = SatelliteAccount::where('page_id', $setting_page_id)->where('nick', $nick)->get();

	        if (count($account) > 0) {
	        	$owner_id = $account[0]->owner_id;
	        	$account_id = $account[0]->id;
	        }

	        //descripcion de streamate bono
	        if ($this->page['id'] == 4) {
	        	$description = $row[0]." ".$row[2]." ".$row[3];
	        }

	        //pagina olecams
	        if ($this->page['id'] == 18) {
	        	$description = $amount." EUR ( ".$this->payment_file->start_date." al ".$this->payment_file->end_date." )";
	        	$amount = $amount * $this->payment_file->euro;
            	$amount = round($amount, 2);
	        }

	        //pagina xvrchat
	        if ($this->page['id'] == 16) {
	        	$description = $amount." Tokens ( ".$this->payment_file->start_date." al ".$this->payment_file->end_date." )";
	        	$amount = $amount * 0.05;
            	$amount = round($amount, 2);
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

	        $file_upload["percent"] = ($total_rows == 1)? 100 : ($i * 100 ) / ($total_rows - 1);
	        $file_upload["completed"] = ($i == $total_rows - 1)? 1 : 0;
	        $file_upload["total_rows"] = $total_rows;
	        $file_upload["i"] = $i;
	        event(new UploadedPayment($file_upload));
    	}

    }

    public function remove_accents($str)
    {
        $from = ["á", "à", "â", "ã", "ä", "é", "è", "ê", "ë", "í", "ì", "î", "ï", "ó", "ò", "ô", "õ", "ö", "ú", "ù", "û", "ü", "ç", "Á", "À", "Â", "Ã", "Ä", "É", "È", "Ê", "Ë", "Í", "Ì", "Î", "Ï", "Ó", "Ò", "Ô", "Õ", "Ö", "Ú", "Ù", "Û", "Ü", "Ç", "┬"];
        $to   = "";
        return str_replace($from, $to, $str);
    }
}
