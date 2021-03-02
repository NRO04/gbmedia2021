<?php

namespace App\Exports\Payroll;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class SalaryIncrease implements WithMultipleSheets
{

	use Exportable;

	public function __construct($increases = null, $range = null)
    {
        $this->increases = $increases;
        $this->range = $range;
    }

    public function sheets(): array
    {
        $sheets = [];

        $sheets[] = new SalarySheet($this->increases["salary"], $this->range);
        $sheets[] = new IncreasesSheet($this->increases["increase"]);
        
        return $sheets;
    }
}
