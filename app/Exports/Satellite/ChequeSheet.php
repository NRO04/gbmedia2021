<?php

namespace App\Exports\Satellite;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class ChequeSheet implements FromArray, WithHeadings, ShouldAutoSize, WithCustomStartCell, WithEvents,  WithTitle, WithColumnFormatting
{
    use Exportable;
    protected $payrolls;
    protected $payment_date;

    public function __construct($payrolls = null, $payment_date = null)
    {
        $this->payrolls = $payrolls;
        $this->payment_date = $payment_date;
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
                $event->sheet->getStyle('A1:H1')->applyFromArray($styleArray);
                $event->sheet->getDelegate()->getStyle('A1:H1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('3C6BFF');
                $event->sheet->getDelegate()->getStyle('A1:H1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $event->sheet->getDelegate()->getStyle('A1:H'.''.(count($this->payrolls) + 1) )->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $event->sheet->getStyle('B2:B'.''.(count($this->payrolls) + 1) )->getNumberFormat()->setFormatCode('###,###,###');
            }];
    }

    public function headings(): array
    {
        return [
            "Propietario",
            "Valor a Pagar",
            "Forma Pago",
            "Titular",
            "Cedula",
            "Direccion",
            "Ciudad",
            "Telefono",
        ];
    }

    public function columnFormats(): array
    {
        return [
            'E' => NumberFormat::FORMAT_NUMBER,
        ];
    }

    public function array(): array
    {
        return $this->payrolls;
    }

    public function startCell(): string
    {
        return 'A1';
    }

    public function title(): string
    {
        return 'Cheque sin Retencion';
    }
}
