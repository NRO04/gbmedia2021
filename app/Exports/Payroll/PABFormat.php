<?php

namespace App\Exports\Payroll;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class PABFormat implements WithMultipleSheets
{

	use Exportable;

	public function __construct($result = null, $range = null)
    {
        $this->result = $result;
        $this->range = $range;
    }

    public function sheets(): array
    {
        $sheets = [];

        $sheets[] = new PABFormatSheet($this->result["pab"], $this->range);
        $sheets[] = new GrupoAvalSheet($this->result["grupo_aval"]);
        $sheets[] = new NoPaymentMethodSheet($this->result["no_payment_method"]);

        return $sheets;
    }
}
