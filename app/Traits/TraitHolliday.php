<?php
namespace App\Traits;
use Carbon\Carbon;
class TraitHolliday
{
    private $today;
	private $holidays;
	private $year;
	private $pascua_month;
    private $pascua_day;

    function __construct(){
        $this->Holidays();
    }
    
    public function Holidays($year = '')
    {
        $this->today = date('d/m/Y');

        if($year == '')
            $year = date('Y');

        $this->year=$year;

        $this->pascua_month  = date("m", easter_date($this->year));
        $this->pascua_da     = date("d", easter_date($this->year));
        
        $this->holidays[$year][1][1]   = true;	// Primero de Enero
		$this->holidays[$year][5][1]   = true;	// Dia del Trabajo 1 de Mayo
		$this->holidays[$year][7][20]  = true;	// Independencia 20 de Julio
		$this->holidays[$year][8][7]   = true;	// Batalla de Boyac� 7 de Agosto
		$this->holidays[$year][12][8]  = true;	// Maria Inmaculada 8 diciembre (religiosa)
        $this->holidays[$year][12][25] = true;     // Navidad 25 de diciembre
        
        $this->calculate_emiliani(1, 6);	// Reyes Magos Enero 6
		$this->calculate_emiliani(3, 19);	// San Jose Marzo 19
		$this->calculate_emiliani(6, 29);	// San Pedro y San Pablo Junio 29
		$this->calculate_emiliani(8, 15);	// Asunci�n Agosto 15
		$this->calculate_emiliani(10, 12);	// Descubrimiento de Am�rica Oct 12
		$this->calculate_emiliani(11, 1);	// Todos los santos Nov 1
		$this->calculate_emiliani(11, 11);	// Independencia de Cartagena Nov 11
    }

    protected function calculate_emiliani($holiday_month,$holiday_day) 
	{
        // funcion que mueve una fecha diferente a lunes al siguiente lunes en el
		// calendario y se aplica a fechas que estan bajo la ley emiliani
		//global  $y,$holiday_day,$mes_festivo,$festivo;
		// Extrae el dia de la semana
        // 0 Domingo � 6 S�bado

        $dd = date("w",mktime(0, 0, 0, $holiday_month, $holiday_day, $this->year));
        switch ($dd) {
            case 0:                                    // Domingo
            $holiday_day = $holiday_day + 1;
            break;
            case 2:                                    // Martes.
            $holiday_day = $holiday_day + 6;
            break;
            case 3:                                    // Mi�rcoles
            $holiday_day = $holiday_day + 5;
            break;
            case 4:                                     // Jueves
            $holiday_day = $holiday_day + 4;
            break;
            case 5:                                     // Viernes
            $holiday_day = $holiday_day + 3;
            break;
            case 6:                                     // S�bado
            $holiday_day = $holiday_day + 2;
            break;
        }
        $month  = date("n", mktime(0,0,0,$holiday_month,$holiday_day,$this->year))+0;
		$day    = date("d", mktime(0,0,0,$holiday_month,$holiday_day,$this->year))+0;
		$this->holidays[$this->year][$month][$day] = true;
    }
    protected function OtherCalculatedDates ($numberOfDays=0,$nextMonday=false)
    {
        $holiday_month  = date("n", mktime(0,0,0,$this->pascua_month,$this->pascua_day+$numberOfDays,$this->year));
        $holiday_day    = date("d", mktime(0,0,0,$this->pascua_month,$this->pascua_day+$numberOfDays,$this->ano));

        if($nextMonday)
        {
            $this->calculate_emiliani($holiday_month,$holiday_day);
        }
        else
        {
            $this->Holidays[$this->year][$holiday_month+0][$holiday_day+0] = true;
        }
    }
    public function isHoliday($date)
    {
        $month  = Carbon::parse($date)->format('n');
        $year   = Carbon::parse($date)->format('Y');
        $day    = Carbon::parse($date)->format('j');

		if($day=='' or $month=='')
		{
			return false;
		}
        
        //dd($this->year);
		if (isset($this->holidays[(int)$year][(int)$month][(int)$day]))
		{
			return true;
		}
		else 
		{
			return false;
		}

    }

}
?>