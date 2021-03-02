<?php

namespace App\Exports\Boutique;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class Sales implements WithMultipleSheets
{
	use Exportable;

    private $sales;
    private $week;

    public function __construct($sales, $week)
    {
        $this->sales = $sales;
        $this->week = $week;
    }

    public function sheets(): array
    {
        $sheets = [];

        $sheets[] = new SalesSheet($this->sales, $this->week);

        return $sheets;
    }
}
