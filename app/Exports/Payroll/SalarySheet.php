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

class SalarySheet implements FromArray, WithHeadings, ShouldAutoSize, WithCustomStartCell, WithEvents,  WithTitle
{
    
	use Exportable;
    protected $increases;
	protected $range;

	public function __construct($increases = null, $range = null)
    {
        $this->increases = $increases;
        $this->range = $range;
    }

    public function registerEvents(): array
    {
        $styleheader = [
            'font' => [
                'bold'  => true,
                'color' => array('rgb' => '5959F3'),
                'size'  => 12,
            ],
        ];

        $styleArray = [
            'font' => [
                'color' => array('rgb' => 'FFFFFF'),
                'size'  => 11,
            ],
        ];

        return [
            AfterSheet::class => function(AfterSheet $event) use ($styleArray, $styleheader) {
                $event->sheet->setCellValue('A1' , 'Salarios Quincena '.$this->range);
                $event->sheet->getStyle('A1:Z1')->applyFromArray($styleheader);
                $event->sheet->mergeCells('A1:Z1');
                $event->sheet->getDelegate()->getStyle('A1:Z1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $event->sheet->getDelegate()->getStyle('A2:I2')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('7487FD');
                $event->sheet->getDelegate()->getStyle('J2:Q2')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('36CE2A');
                $event->sheet->getDelegate()->getStyle('R2:Y2')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('F33C52');
                $event->sheet->getDelegate()->getStyle('Z2')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('2E2ED0');
                $event->sheet->getStyle('A2:Z2')->applyFromArray($styleArray);
                $event->sheet->getDelegate()->getStyle('A2:Z2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $event->sheet->getDelegate()->getStyle('A1:Z'.''.(count($this->increases) + 1) )->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $event->sheet->getStyle('G2:Z'.''.(count($this->increases) + 1) )->getNumberFormat()->setFormatCode('###,###,###');
        }];
    }

    public function headings(): array
    {
    	return [
    		"Nombre",
    		"Cargo",
            "Fecha Inicio",
            "Identificacion",
            "Direccion",
    		"Telefono",
            "Salario M",
            "Dias Trabajados",
            "Salario Q",
            "Horas Extras",
            "Recargo Nocturno",
            "Comisiones",
            "Auxilio Movilizacion",
            "Re-Record",
            "Bonificacion",
            "Auxilio Transporte",
            "Total Devengado",
            "Seguridad Social",
            "Prestamo",
            "Cafeteria",
            "Nevera",
            "Boutique",
            "Otros",
            "Llegada Tarde",
            "Total Deducciones",
    		"Neto a Pagar",
    	];
    }

    public function array(): array
    {
       	return $this->increases;
    }

    public function startCell(): string
    {
        return 'A2';
    }

    public function title(): string
    {
        return 'Salarios';
    }
    
}
