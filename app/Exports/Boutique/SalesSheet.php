<?php

namespace App\Exports\Boutique;

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

class SalesSheet implements FromArray, WithHeadings, ShouldAutoSize, WithEvents, WithTitle, WithCustomStartCell
{
    use Exportable;

    protected $sales;
    protected $week;

    public function __construct($sales = null, $week = null)
    {
        $this->sales = $sales;
        $this->week = $week;
    }

    public function registerEvents(): array
    {
        $styleArray = [
            'font' => [
                'color' => array('rgb' => 'FFFFFF'),
                'size' => 14,
            ],
        ];

        $styleWhite = [
            'font' => [
                'color' => array('rgb' => 'FFFFFF'),
                'size' => 12,
            ],
        ];

        return [
            AfterSheet::class => function (AfterSheet $event) use ($styleArray, $styleWhite) {
                $event->sheet->setCellValue('A1', "Ventas de Boutique | $this->week");
                $event->sheet->mergeCells('A1:F1');

                $event->sheet->getDelegate()->getStyle('A1:F1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('299c70');
                $event->sheet->getDelegate()->getStyle('A3:F3')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('7487FD');

                $event->sheet->getStyle('A1:F1')->applyFromArray($styleArray);
                $event->sheet->getStyle('A3:F3')->applyFromArray($styleWhite);

                $event->sheet->getDelegate()->getStyle('A1:F1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $event->sheet->getDelegate()->getStyle('A3:F' . '' . (count($this->sales) + 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            }];
    }

    public function headings(): array
    {
        return [
            "Producto",
            "Cantidad",
            "Total Venta",
            "Comprador",
            "Vendedor",
            "Fecha",
        ];
    }

    public function array(): array
    {
        return (array)$this->sales;
    }

    public function title(): string
    {
        return $this->week;
    }

    public function startCell(): string
    {
        return 'A3';
    }
}
