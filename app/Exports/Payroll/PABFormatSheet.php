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

class PABFormatSheet implements FromArray, WithHeadings, ShouldAutoSize, WithCustomStartCell, WithEvents,  WithTitle
{

	use Exportable;
    protected $pab;
	protected $range;

	public function __construct($pab = null, $range = null)
    {
        $this->pab = $pab;
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
                $event->sheet->getDelegate()->getStyle('A1:G1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('1C7296');
                $event->sheet->setCellValue('A1' , 'NIT PAGADOR');
                $event->sheet->setCellValue('A2' , '901145956');
                $event->sheet->setCellValue('B1' , 'TIPO PAGO');
                $event->sheet->setCellValue('B2' , '220');
                $event->sheet->setCellValue('C1' , 'APLICACION');
                $event->sheet->setCellValue('D1' , 'SECUENCIA DE ENVIO');
                $event->sheet->setCellValue('D2' , 'A2');
                $event->sheet->setCellValue('E1' , 'NRO CUENTA A DEBITAR');
                $event->sheet->setCellValue('E2' , '82992638439');
                $event->sheet->setCellValue('F1' , 'TIPO CUENTA A DEBITAR');
                $event->sheet->setCellValue('F2' , 'D');
                $event->sheet->setCellValue('G1' , 'DESCRIPCION DEL PAGO');
                $event->sheet->setCellValue('G2' , '20190806A');
                $event->sheet->getStyle('A1:G1')->applyFromArray($styleheader);
                $event->sheet->getDelegate()->getStyle('A1:G1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $event->sheet->getDelegate()->getStyle('A3:L3')->getFill()
                    ->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('1C7296');
                $event->sheet->getStyle('A3:L3')->applyFromArray($styleheader);
                $event->sheet->getDelegate()->getStyle('A3:L3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }];
    }

    public function headings(): array
    {
    	return [
    		"Tipo Documento Beneficiario",
    		"Nit Beneficiario",
            "Nombre Beneficiario",
            "Tipo Transaccion",
            "Codigo Banco",
    		"Nro Cuenta Beneficiario",
            "Email",
            "Documento Autorizado",
            "Referencia",
            "Oficina Entrega",
            "Valor Transaccion",
            "Fecha de Aplicacion",
    	];
    }

    public function array(): array
    {
       	return $this->pab;
    }

    public function startCell(): string
    {
        return 'A3';
    }

    public function title(): string
    {
        return 'Formato PAB';
    }

}
