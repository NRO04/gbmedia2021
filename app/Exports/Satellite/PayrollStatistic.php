<?php

namespace App\Exports\Satellite;

use App\Exports\Satellite\PayrollAccountsSheet;
use App\Exports\Satellite\PayrollCommissionsSheet;
use App\Exports\Satellite\PayrollDeductionsSheet;
use App\Exports\Satellite\PayrollSheet;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class PayrollStatistic implements WithMultipleSheets
{

    use Exportable;

    public function __construct($payrolls = null)
    {
        $this->payrolls = $payrolls;
    }

    public function sheets(): array
    {
        $sheets = [];

        $sheets[] = new PayrollSheet($this->payrolls["payroll"]);
        $sheets[] = new PayrollAccountsSheet($this->payrolls["accounts"]);
        $sheets[] = new PayrollCommissionsSheet($this->payrolls["commissions"]);
        $sheets[] = new PayrollDeductionsSheet($this->payrolls["deductions"]);

        return $sheets;
    }
}
