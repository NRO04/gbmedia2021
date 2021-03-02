<?php

namespace App\Imports\Satellite\Payment;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;

use Auth;
use App\Models\Satellite\SatelliteAccount;
use App\Models\Satellite\SatellitePaymentAccount;
use App\Events\UploadedPayment;

class PaymentCSVSkyprivate implements ToCollection, WithCustomCsvSettings
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
        $created_by = Auth::user()->id;
        
        $file_upload["file"] = $this->page['id'] - 1;
        
        $new_collection = [];
        $cont = 0;

        for ($i=1; $i < $total_rows; $i++) { 
            
            $row = $collection[$i][0];
            $row = explode(",", $row);
            $exists = false;

            $live_id = trim($row[$this->page->cell_nick]);
            $live_id = str_replace('"', "", $live_id);
            $amount = str_replace("$", "", $row[$this->page->cell_value]);
            $amount  = trim($amount);
            $amount = str_replace(",", "", $amount);
            $amount = str_replace('"', "", $amount); 
            $amount = ($amount * 75) / 100;
            $amount = round($amount,2);

            $description  = trim($row[4]).", ".trim($row[5]);
            $description = str_replace('"', "", $description);   
            $description = "($".$amount.")"."(".$description.")"; 

            if (count($new_collection) > 0) {
                for ($j=0; $j < $cont; $j++) { 
                
                    if ($live_id == $new_collection[$j]['live_id']) {

                        $new_collection[$j]['amount'] = $new_collection[$j]['amount'] + $amount;      
                        $new_collection[$j]['description'] = $new_collection[$j]['description']." ".$description;  
                        $exists = true;
                        break;         
                    } 
                }
            }

            if ($exists == false){
                
                $new_collection[$cont]['live_id'] = $live_id;
                $new_collection[$cont]['amount'] = $amount;      
                $new_collection[$cont]['description'] = $description;  
                $cont++;
            }
        }

        for ($i=0; $i < $cont ; $i++) { 
            
            $nick = null;
            $owner_id = null;
            $account_id = null;

            $account = SatelliteAccount::where('page_id', $setting_page_id)->where('live_id', $new_collection[$i]["live_id"])->get();
            
            if (count($account) > 0) {
                $nick = $account[0]->nick;
                $owner_id = $account[0]->owner_id;
                $account_id = $account[0]->id;
            }

            $payment_account = new SatellitePaymentAccount([
                'owner_id' => $owner_id,
                'page_id' => $setting_page_id,
                'account_id' => $account_id,
                'file_id' => $file_id,
                'payment_date' => $payment_date,
                'amount' => $new_collection[$i]["amount"],
                'nick' => $nick,
                'live_id' => $new_collection[$i]["live_id"],
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
            'delimiter' => ",",
        ];
    }
}
