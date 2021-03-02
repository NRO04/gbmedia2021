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

class IncreasesSheet implements FromArray, WithHeadings, ShouldAutoSize, WithEvents,  WithTitle
{

	use Exportable;
	protected $increases;

	public function __construct($increases = null)
    {
        $this->increases = $increases;
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
                $event->sheet->getDelegate()->getStyle('A1:E1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('7487FD');
                $event->sheet->getStyle('A1:E1')->applyFromArray($styleArray);
                $event->sheet->getDelegate()->getStyle('A1:E1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $event->sheet->getDelegate()->getStyle('A1:E'.''.(count($this->increases) + 1) )->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $event->sheet->getStyle('D2:D'.''.(count($this->increases) + 1) )->getNumberFormat()->setFormatCode('###,###,###');
        }];
    }

    public function headings(): array
    {
    	return [
    		"Nombre",
    		"Cargo",
    		"Fecha Inicio",
    		"Salario M",
    		"Ultimo Incremento",
    	];
    }

    public function array(): array
    {
       	return $this->increases;
    }

    public function title(): string
    {
        return 'Aumentos';
    }

}
