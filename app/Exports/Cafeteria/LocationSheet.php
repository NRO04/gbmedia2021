<?php

namespace App\Exports\Cafeteria;

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

class LocationSheet implements FromArray, WithHeadings, ShouldAutoSize, WithCustomStartCell, WithEvents, WithTitle
{
    use Exportable;

    protected $location_name;
    protected $orders;
    protected $menu_description;
    protected $menu_type;

    public function __construct($location, $menu_type, $menu_description, $orders)
    {
        $this->location_name = $location;
        $this->orders = $orders;
        $this->menu_description = $menu_description;
        $this->menu_type = $menu_type;
    }

    public function registerEvents(): array
    {
        $styleheader = [
            'font' => [
                'bold' => true,
                'color' => array('rgb' => '5959F3'),
                'size' => 12,
            ],
        ];

        $styleArray = [
            'font' => [
                'color' => array('rgb' => 'FFFFFF'),
                'size' => 11,
            ],
        ];

        return [
            AfterSheet::class => function (AfterSheet $event) use ($styleArray, $styleheader) {
                $event->sheet->setCellValue('A1', "Pedidos $this->menu_type | $this->location_name");
                $event->sheet->mergeCells('A1:C1');
                $event->sheet->setCellValue('A2', "Menú: $this->menu_description");
                //$event->sheet->mergeCells('A2:C2');
                $event->sheet->setCellValue('A3', "");
                $event->sheet->mergeCells('A3:C3');

                $event->sheet->getDelegate()->getStyle('A1:C1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('299c70');
                $event->sheet->getDelegate()->getStyle('A2:C2')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('299c70');
                $event->sheet->getDelegate()->getStyle('A4:C4')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('0080D3');

                //$event->sheet->getDelegate()->getStyle('A1:C'.''.(count($this->orders) + 2))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                 //$event->sheet->getDelegate()->getStyle('A2:C'.''.(count($this->orders) + 2))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $event->sheet->getStyle('A1:C1')->applyFromArray($styleArray);
                $event->sheet->getStyle('A2:C2')->applyFromArray($styleArray);
                $event->sheet->getStyle('A4:C4')->applyFromArray($styleArray);
            }];
    }

    public function headings(): array
    {
        return [
            "Usuario",
            "Observaciones",
            "Cantidad",
        ];
    }

    public function array(): array
    {
        return $this->orders;
    }

    public function startCell(): string
    {
        return 'A4';
    }

    public function title(): string
    {
        return $this->location_name;
    }

}
