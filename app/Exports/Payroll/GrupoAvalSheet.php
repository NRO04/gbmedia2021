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

class GrupoAvalSheet implements FromArray, WithHeadings, ShouldAutoSize, WithCustomStartCell, WithEvents,  WithTitle
{

	use Exportable;
    protected $grupo_aval;
	protected $range;

	public function __construct($grupo_aval = null, $range = null)
    {
        $this->grupo_aval = $grupo_aval;
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
                $event->sheet->getDelegate()->getStyle('A1:I1')->getFill()
                    ->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('1C7296');
                $event->sheet->getStyle('A1:I1')->applyFromArray($styleheader);
                $event->sheet->getDelegate()->getStyle('A1:I1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }];
    }

    public function headings(): array
    {
    	return [
    		"PROPIETARIO",
    		"Nro Identificacion",
            "Tipo Doc",
            "Nombres Apellidos",
            "Banco",
            "Tipo Cuenta",
            "Nro Cuenta",
            "Email",
            "Valor Pago",
    	];
    }

    public function array(): array
    {
       	return $this->grupo_aval;
    }

    public function startCell(): string
    {
        return 'A1';
    }

    public function title(): string
    {
        return 'Grupo Aval';
    }

}
