<?php

namespace App\Exports\Satellite;

use App\Exports\Satellite\AVVillasOthersSheet;
use App\Exports\Satellite\BancolombiaNewSheet;
use App\Exports\Satellite\BancolombiaOthersSheet;
use App\Exports\Satellite\BancolombiaSheet;
use App\Exports\Satellite\BancoUsaSheet;
use App\Exports\Satellite\ChequeSheet;
use App\Exports\Satellite\EfectySheet;
use App\Exports\Satellite\GrupoAvalNewSheet;
use App\Exports\Satellite\GrupoAvalSheet;
use App\Exports\Satellite\NoPaymentMethodsSheet;
use App\Exports\Satellite\PaxumSheet;
use App\Exports\Satellite\WesternUnionSheet;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class WithBancolombia implements WithMultipleSheets
{

    use Exportable;

    public function __construct($payrolls = null, $range = null)
    {
        $this->payrolls = $payrolls;
        $this->range = $range;
    }

    public function sheets(): array
    {
        $sheets = [];

        $sheets[] = new NoPaymentMethodsSheet($this->payrolls["sin_fp"]);
        $sheets[] = new BancolombiaSheet($this->payrolls["bancolombia"]);
        $sheets[] = new GrupoAvalSheet($this->payrolls["grupo_aval"]);
        $sheets[] = new EfectySheet($this->payrolls["efecty"]);
        $sheets[] = new PaxumSheet($this->payrolls["paxum"]);
        $sheets[] = new ChequeSheet($this->payrolls["cheque"]);
        $sheets[] = new BancoUsaSheet($this->payrolls["usa"]);
        $sheets[] = new WesternUnionSheet($this->payrolls["western_union"]);
        $sheets[] = new BancolombiaOthersSheet($this->payrolls["bancolombia_others"]);
        $sheets[] = new BancolombiaNewSheet($this->payrolls["bancolombia_new"]);
        $sheets[] = new GrupoAvalNewSheet($this->payrolls["grupo_aval_new"]);

        return $sheets;
    }
}
