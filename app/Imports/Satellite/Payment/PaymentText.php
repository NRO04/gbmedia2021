<?php

namespace App\Imports\Satellite\Payment;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

use Auth;
use App\Models\Satellite\SatelliteAccount;
use App\Models\Satellite\SatellitePaymentAccount;
use App\Events\UploadedPayment;

class PaymentText implements ToCollection
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
    	if ($this->page['id'] == 11) {
    		$this->xloveCam($collection);
    	}
    	else{
    		$this->xloveCamBono($collection);
    	}
    }

    public function xloveCam(Collection $collection)
    {
    	$payment_date = $this->payment_file->payment_date;
        $file_id = $this->payment_file->id;
        $setting_page_id = $this->page->setting_page_id;
        $created_by = Auth::user()->id;

        $file_upload["file"] = $this->page['id'] - 1;

    	$new_collection = [];
        $cont = 0;

    	for ($i=0; $i < count($collection) ; $i++) {

    		$data = $collection[$i];

    		if ($data[1] == null) {
    			$cont++;
    			$new_collection[$cont]["nick"] =  $data[0];
    			$new_collection[$cont]["amount"] =  0;
    			$new_collection[$cont]["description"] = "";
    		}
    		else{

    			$explode = explode("\t", $data[2]);
                if ($explode[count($explode) - 1] == "ok")
                {
                    $amount = $explode[count($explode) - 2];
                }
                else{
                    $amount = $explode[count($explode) - 1];
                }

	            $amount = str_replace('"', "", $amount);
	            $amount = str_replace('€', "", $amount);

    			$amount  = trim($amount);

    			if (is_numeric($amount)) {
                    $new_collection[$cont]["amount"] = $new_collection[$cont]["amount"] + $amount;
    				$new_collection[$cont]["description"] .= "<br>".str_replace("\t", " ", $data[0])." ".str_replace("\t", " ", $data[1])." ".str_replace("\t", "", $data[2]);
                }
    		}
    	}

    	for ($i=1; $i < $cont ; $i++) {

            $owner_id = null;
            $account_id = null;
            $amount = round($new_collection[$i]["amount"], 2);
            $description = $amount." EUR --> ".$new_collection[$i]["description"];
            $amount = $amount * $this->payment_file->euro;
            $amount = round($amount, 2);

            $account = SatelliteAccount::where('page_id', $setting_page_id)->where('nick', $new_collection[$i]["nick"])->get();

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
                'nick' => $new_collection[$i]["nick"],
                'description' => $description,
                'created_by' => $created_by,
            ]);

            $payment_account->save();

            $file_upload["percent"] = ($i * 100 ) / ($cont - 1);
            $file_upload["completed"] = ($i == $cont - 1)? 1 : 0;
            $file_upload["total_rows"] = $cont;
            $file_upload["i"] = $i;
            event(new UploadedPayment($file_upload));
    	}
    }

    public function xloveCamBono(Collection $collection)
    {

    	$payment_date = $this->payment_file->payment_date;
        $file_id = $this->payment_file->id;
        $setting_page_id = $this->page->setting_page_id;
        $created_by = Auth::user()->id;

        $file_upload["file"] = $this->page['id'] - 1;

    	$new_collection = [];
        $cont = 0;

    	for ($i=0; $i < count($collection) ; $i++) {

    		$data = explode("\t", $collection[$i][0]);

    		if (!isset($data[1])) {
    			$cont++;
    			$new_collection[$cont]["nick"] =  str_replace('"', "", $data[0]);
    			$new_collection[$cont]["amount"] =  0;
    			$new_collection[$cont]["description"] = "";
    		}
    		else{

    			for ($j=count($data) -1 ; $j > 0 ; $j--) {

	    			if (is_numeric($data[$j])) {

	    				$amount = $data[$j];
	    				$amount = str_replace('"', "", $amount);
	    				$amount  = trim($amount);
	    				$amount  = round($amount, 2);

	    				if ($amount == 0) {
	    					$amount = $data[$j-1] - $data[$j-2];
	    					$amount  = round($amount, 2);
	    				}

	                    $new_collection[$cont]["amount"] = $amount;
	                    $new_collection[$cont]["description"] = trim($new_collection[$cont]["description"]);
	    				break;
	                }
	                else{
	                	$new_collection[$cont]["description"] = str_replace("\t", "", $data[$j])." ".$new_collection[$cont]["description"];
	                }
    			}
    		}
    	}
    	/*dd($new_collection);*/
    	for ($i=1; $i < $cont ; $i++) {

            $owner_id = null;
            $account_id = null;
            $amount = $new_collection[$i]["amount"];
            $amount = $amount * $this->payment_file->euro;
            $amount = round($amount, 2);
            /*dd($amount);
            dd($this->payment_file->euro);*/
            $account = SatelliteAccount::where('page_id', $setting_page_id)->where('nick', $new_collection[$i]["nick"])->get();

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
                'nick' => $new_collection[$i]["nick"],
                'description' => $new_collection[$i]["description"],
                'created_by' => $created_by,
            ]);

            $payment_account->save();

            $file_upload["percent"] = ($i * 100 ) / ($cont - 1);
            $file_upload["completed"] = ($i == $cont - 1)? 1 : 0;
            $file_upload["total_rows"] = $cont;
            $file_upload["i"] = $i;
            event(new UploadedPayment($file_upload));
    	}
    }

    public function getCsvSettings(): array
    {
        return [
            'delimiter' => "\t",
        ];
    }
}
