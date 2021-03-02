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

class InventorySheet implements FromArray, WithHeadings, ShouldAutoSize, WithEvents, WithTitle, WithCustomStartCell
{
    use Exportable;

    protected $products;
    protected $locations;
    protected $date;

    public function __construct($products = null, $locations = null, $date = null)
    {
        $this->products = $products;
        $this->locations = $locations;
        $this->date = $date;
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

//        dd(count($this->products));

        return [
            AfterSheet::class => function (AfterSheet $event) use ($styleArray, $styleWhite) {
                $event->sheet->setCellValue('A1', "Inventario de Productos Boutique | $this->date");
                $event->sheet->mergeCells('A1:F1');

                $event->sheet->getDelegate()->getStyle('A1:F1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('299c70');
                $event->sheet->getDelegate()->getStyle('A3:F3')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('7487FD');

                $event->sheet->getStyle('A1:F1')->applyFromArray($styleArray);
                $event->sheet->getStyle('A3:F3')->applyFromArray($styleWhite);

                $event->sheet->getDelegate()->getStyle('A1:F1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $event->sheet->getDelegate()->getStyle('A3:F' . '' . (count($this->products) + 3))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            }];
    }

    public function headings(): array
    {
        $headings = [
            "Producto",
        ];

        foreach ($this->locations AS $location) {
            array_push($headings, $location->name);
        }

        return $headings;
    }

    public function array(): array
    {
//        dd($this->products);
        return (array)$this->products;
    }

    public function title(): string
    {
        return "Inventario de Productos Boutique | $this->date";
    }

    public function startCell(): string
    {
        return 'A3';
    }
}
