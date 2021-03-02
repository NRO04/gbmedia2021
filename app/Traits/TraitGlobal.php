<?php

namespace App\Traits;

use App\Models\Settings\SettingLocation;
use App\Models\Settings\SettingLocationPermission;
use Carbon\Carbon;
use Carbon\CarbonInterval;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\User;
use stdClass;
use Storage;

trait TraitGlobal
{
    public function uploadFile(UploadedFile $file, $folder)
    {
        $tenant_id = tenant('studio_slug');

        if($tenant_id == 'gb') {
            $tenant_id = 'GB';
        }

        //$folder = 'public/'.tenant('id').'/'.$folder."/";
        $folder = "public/$tenant_id/$folder/";

        $filename = uniqid() . '_' . time() . '.' . $file->getClientOriginalExtension();
        $file->storeAs($folder, $filename);
        return $filename;
    }

    public function folderExists($folder)
    {
        $folder = "/".tenant('studio_slug')."/".$folder;
        if (!Storage::exists($folder)) {
        	Storage::disk('public')->makeDirectory($folder);
        }
    }

    public function tenantUploadFile(UploadedFile $file, $folder, $tenant_slug, $file_name = '')
    {
        //asset("storage/" . tenant('studio_name') . "/logo/" . tenant('studio_logo'));
        $folder =  "$tenant_slug/$folder";
        $filename = uniqid() . '_' . time() . '.' . $file->getClientOriginalExtension();

        if (!Storage::exists($folder)) {
            Storage::disk('public')->makeDirectory($folder);
        }

        if (!empty($file_name)) {
            $filename = $file_name . '.' . $file->getClientOriginalExtension();
        }

        $file->storeAs("public/$folder", $filename);

        return $filename;
    }

    public function tenantAsset($asset)
    {
        $tenant_id = tenant('studio_slug');

        if($tenant_id == 'gb') {
            $tenant_id = 'GB';
        }

        return asset("storage/$tenant_id/$asset");
    }

    public function deleteFile($file, $folder)
    {
        $folder = 'public/'.tenant('studio_slug').'/'.$folder."/".$file;
        Storage::delete($folder);
    }

    public function uploadPublicImage(UploadedFile $file, $folder)
    {
        $filename = uniqid() . '_' . time() . '.' . $file->getClientOriginalExtension();
        Storage::disk('public_images')->putFileAs($folder, $file, $filename);
        return $filename;
    }

    public function convertToPesos($value)
    {
        $result = number_format($value, 0, ',', '.');
        return $result;
    }

    function removeAccents($string)
    {
        $not_allowed = array("á", "é", "í", "ó", "ú", "Á", "É", "Í", "Ó", "Ú", "ñ", "À", "Ã", "Ì", "Ò", "Ù", "Ã™", "Ã ", "Ã¨", "Ã¬", "Ã²", "Ã¹", "ç", "Ç", "Ã¢", "ê", "Ã®", "Ã´", "Ã»", "Ã‚", "ÃŠ", "ÃŽ", "Ã”", "Ã›", "ü", "Ã¶", "Ã–", "Ã¯", "Ã¤", "«", "Ò", "Ã", "Ã„", "Ã‹", "(", ")", "-", "¿", "?", "¡", "!");
        $allowed = array("a", "e", "i", "o", "u", "A", "E", "I", "O", "U", "n", "N", "A", "E", "I", "O", "U", "a", "e", "i", "o", "u", "c", "C", "a", "e", "i", "o", "u", "A", "E", "I", "O", "U", "u", "o", "O", "i", "a", "e", "U", "I", "A", "E");

        return str_replace($not_allowed, $allowed, $string);
    }

    function userLocationAccess()
    {
        $locations = [];
        $user_location_id = Auth::user()->setting_location_id;

        $location_permissions = SettingLocationPermission::where('setting_location_id', $user_location_id)->where('id', '!=', 1)->with('Location')->get();

        foreach ($location_permissions AS $location) {
            $locations[] = (object) [
                'id' => $location->Location->id,
                'name' => $location->Location->name,
            ];
        }

        return (object)$locations;
    }

    public function traitSearchCodigoUnicoUser()
    {
        $random_letters = substr(str_shuffle("123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz"), 0, 15);
        $code = $random_letters;

        $exists = User::where('unique_code', $code)->exists();
        if ($exists) {
            $this->traitSearchCodigoUnicoUser();
        }

        return $code;
    }

    public function traitSearchCodigoUnicoUserold()
    {
        while(true)
        {
            $code = "";
            $parameters = "123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";
            $max = strlen($parameters)-1;
            for ($k=0; $k < 15 ; $k++)
                $code.=$parameters(mt_rand(0,$max));

            $exist = User::where('unique_code',$code)->exists();

            if($exist == false)
                break;
        }

        return $code;
    }

    public function getDistinctWeeksBetweenDates($min_date, $max_date)
    {
        $range_weeks = [];

        $min_week_day = date("w", strtotime($min_date));
        $max_week_day = date("w", strtotime($max_date));

        if ($min_week_day == 0) // If is Sunday
        {
            $start_date = $min_date;
        } else // Get last Sunday
        {
            $start_date = Carbon::createFromTimeStamp(strtotime("last Sunday", strtotime($min_date)));
        }

        if ($max_week_day == 6) // If is Saturday
        {
            $end_date = $max_date;
        } else // Get next Saturday
        {
            $end_date = Carbon::createFromTimeStamp(strtotime("next Saturday", strtotime($max_date)));
        }

        while ($start_date < $end_date) {
            $week_start_date = date("Y-m-d", strtotime("-6 day", strtotime($end_date)));

            $range_weeks[] = (object)[
                'start' => Carbon::parse($week_start_date)->format('Y-m-d'),
                'end' => Carbon::parse($end_date)->format('Y-m-d'),
                'formatted' => Carbon::parse($week_start_date)->format('d/M/Y') . " - " . Carbon::parse($end_date)->format('d/M/Y'),
            ];

            $end_date = date("Y-m-d", strtotime("-7 day", strtotime($end_date)));
        }

        return $range_weeks;
    }

    public function getSundaysInMonth($min_date, $max_date)
    {
        $date_from = new \DateTime($min_date);
        $date_to = new \DateTime($max_date);
        $dates = [];

        if ($date_from > $date_to) {
            return $dates;
        }

        if (1 != $date_from->format('N')) {
            $date_from->modify('next sunday');
        }

        while ($date_from <= $date_to) {
            $dates[] = $date_from->format('Y-m-d');
            $date_from->modify('+1 week');
        }

        return $dates;
    }

    function getAllDaysInAMonth($year, $month, $day = "", $daysError = 3) {
        $dateString = 'first '.$day.' of '.$year.'-'.$month;

        if (!strtotime($dateString)) {
            throw new \Exception('"'.$dateString.'" is not a valid strtotime');
        }

        $startDay = new \DateTime($dateString);

        if ($startDay->format('j') > $daysError) {
            $startDay->modify('- 7 days');
        }

        $days = array();

        while ($startDay->format('Y-m') <= $year.'-'.str_pad($month, 2, 0, STR_PAD_LEFT)) {
            $days[] = clone($startDay);
            $startDay->modify('+ 7 days');
        }

        return $days;
    }

    public function getDaysInRange($dateFromString, $dateToString, $day)
    {
        $dateFrom = new \DateTime($dateFromString);
        $dateTo = new \DateTime($dateToString);
        $dates = [];

        if ($dateFrom > $dateTo) {
            return $dates;
        }

        if (1 != $dateFrom->format('N')) {
            $dateFrom->modify('next '.$day);
        }

        while ($dateFrom <= $dateTo) {
            $dates[] = $dateFrom->format('Y-m-d');
            $dateFrom->modify('+1 week');
        }

        return $dates;
    }

    function convertToMinsHours($time){

        $hours    = floor($time / 60);
        $minutes  = ($time % 60);

        if($minutes == 0){

            if($hours == 1){

                $output_format = 'Ahora ';

            }else{

                $output_format = 'Ahora ';
            }


            $hoursToMinutes = sprintf($output_format, $hours);

        }else if($hours == 0){

            if ($minutes < 10) {
                $minutes = $minutes;
            }

            if($minutes == 1){

                $output_format  = ' %2dm ';

            }else{

                $output_format  = ' %2dm ';
            }

            $hoursToMinutes = sprintf($output_format,  $minutes);

        }else {

            if($hours == 1){

                $output_format = '%02dhr %02dm';

            }else{

                $output_format = '%02dhrs %02dm';
            }

            $hoursToMinutes = sprintf($output_format, $hours, $minutes);
        }

        return $hoursToMinutes;
    }

    public function getBaseLocation()
    {
        $base_location = new stdClass();

        $locations = SettingLocation::where('name', '!=', 'All')->get();

        foreach ($locations AS $location) {
            if($location->base != 1) { continue; }

            $base_location->base_location_id = $location->id;
            $base_location->base_location_name = $location->name;
        }

        return $base_location;
    }

    function excerpt($string, $length, $end = '...')
    {
        $string = strip_tags($string);

        if (strlen($string) > $length) {

            // truncate string
            $stringCut = substr($string, 0, $length);

            // make sure it ends in a word so assassinate doesn't become ass...
            $string = substr($stringCut, 0, strrpos($stringCut, ' ')) . $end;
        }
        return $string;
    }

    function accents($string)
    {
        $search = explode(",", "á,é,í,ó,ú,ñ,Á,É,Í,Ó,Ú,Ñ,Ã¡,Ã©,Ã­,Ã³,Ãº,Ã±,ÃÃ¡,ÃÃ©,ÃÃ­,ÃÃ³,ÃÃº,ÃÃ±,Ã“,Ã ,Ã‰,Ã ,Ãš,â€œ,â€ ,Â¿,ü");
        $replace = explode(",", "á,é,í,ó,ú,ñ,Á,É,Í,Ó,Ú,Ñ,á,é,í,ó,ú,ñ,Á,É,Í,Ó,Ú,Ñ,Ó,Á,É,Í,Ú,\",\",¿,&uuml;");
        $string = str_replace($search, $replace, $string);

        return $string;
    }
}
