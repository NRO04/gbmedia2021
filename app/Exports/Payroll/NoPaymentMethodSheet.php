<?php

namespace App\Exports\Payroll;

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

class NoPaymentMethodSheet implements FromArray, WithHeadings, ShouldAutoSize, WithCustomStartCell, WithEvents,  WithTitle
{

	use Exportable;
    protected $no_payment_method;
	protected $range;

	public function __construct($no_payment_method = null, $range = null)
    {
        $this->no_payment_method = $no_payment_method;
        $this->range = $range;
    }

    public function registerEvents(): array
    {
        $styleheader = [
            'font' => [
                'bold'  => true,
                'color' => array('rgb' => 'FDFDFD'),
                'size'  => 10,
            ],
        ];

        $styleArray = [
            'font' => [
                'color' => array('rgb' => 'FDFDFD'),
                'size'  => 11,
            ],
        ];

        return [
            AfterSheet::class => function(AfterSheet $event) use ($styleArray, $styleheader) {
                $event->sheet->getDelegate()->getStyle('A1:B1')->getFill()
                    ->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('1C7296');
                $event->sheet->getStyle('A1:B1')->applyFromArray($styleheader);
                $event->sheet->getDelegate()->getStyle('A1:B1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }];
    }

    public function headings(): array
    {
    	return [
    		"Nombre Beneficiario",
            "Valor a Pagar",
    	];
    }

    public function array(): array
    {
       	return $this->no_payment_method;
    }

    public function startCell(): string
    {
        return 'A1';
    }

    public function title(): string
    {
        return 'Sin Forma de Pago';
    }

}
