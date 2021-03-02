<?php

namespace App\Exports\Satellite;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class PayrollAccountsSheet implements FromArray, WithHeadings, ShouldAutoSize, WithCustomStartCell, WithEvents,  WithTitle
{
    use Exportable;
    public $accounts;

    public function __construct($accounts = null)
    {
        $this->accounts = $accounts;
    }

    public function registerEvents(): array
    {
        $styleArray = [
            'font' => [
                'color' => array('rgb' => 'FFFFFF'),
                'size'  => 12,
            ],
        ];
        return [
            AfterSheet::class => function(AfterSheet $event) use ($styleArray) {
                $event->sheet->getStyle('A1:E1')->applyFromArray($styleArray);
                $event->sheet->getDelegate()->getStyle('A1:E1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('3C6BFF');
                $event->sheet->getDelegate()->getStyle('A1:E1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $event->sheet->getDelegate()->getStyle('A1:E'.''.(count($this->accounts) + 1) )->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            }];
    }

    public function headings(): array
    {
        return [
            "Fecha Pago",
            "Página",
            "Nick",
            "Valor (USD)",
            "Descripción",
        ];
    }

    public function array(): array
    {
        return $this->accounts;
    }

    public function startCell(): string
    {
        return 'A1';
    }

    public function title(): string
    {
        return 'Cuentas';
    }
}
