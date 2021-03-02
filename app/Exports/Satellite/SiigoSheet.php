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

class SiigoSheet implements FromArray, WithHeadings, ShouldAutoSize, WithCustomStartCell, WithEvents,  WithTitle, WithColumnFormatting
{
    use Exportable;
    protected $payrolls;

    public function __construct($payrolls = null)
    {
        $this->payrolls = $payrolls;
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
                $event->sheet->getStyle('A1:CK1')->applyFromArray($styleArray);
                $event->sheet->getDelegate()->getStyle('A1:CK1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('3C6BFF');
                $event->sheet->getDelegate()->getStyle('A1:CK1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $event->sheet->getDelegate()->getStyle('A1:CK'.''.(count($this->payrolls) + 1) )->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            }];
    }

    public function headings(): array
    {
        return [
            'TIPO COMPROBANTE',
            'CODIGO COMPROBANTE',
            'NRO DOCUMENTO',
            'CUENTA CONTABLE',
            'DÉBITO O CRÉDITO',
            'VALOR DE LA SECUENCIA',
            'AÑO DEL DOCUMENTO',
            'MES DEL DOCUMENTO',
            'DIA DEL DOCUMENTO',
            'CÓDIGO DEL VENDEDOR',
            'CÓDIGO DE LA CIUDAD',
            'CÓDIGO DE LA ZONA',
            'SECUENCIA',
            'CENTRO DE COSTO',
            'SUBCENTRO DE COSTO',
            'NIT - NUMERO DE CEDULA- PASAPORTE',
            'SUCURSAL',
            'DESCRIPCIÓN DE LA SECUENCIA',
            'NÚMERO DE CHEQUE',
            'COMPROBANTE ANULADO',
            'CÓDIGO DEL MOTIVO DE DEVOLUCIÓN',
            'FORMA DE PAGO',
            'VALOR DEL CARGO 1 DE LA SECUENCIA',
            'VALOR DEL CARGO 2 DE LA SECUENCIA',
            'VALOR DEL DESCUENTO 1 DE LA SECUENCIA',
            'VALOR DEL DESCUENTO 2 DE LA SECUENCIA',
            'VALOR DEL DESCUENTO 3 DE LA SECUENCIA',
            'FACTURA ELECTRÓNICA A DEBITAR/ACREDITAR',
            'NÚMERO DE FACTURA ELECTRÓNICA A DEBITAR/ACREDITAR',
            'PREFIJO DE ORDER REFERENCE',
            'CONSECUTIVO DE ORDER REFERENCE',
            'PORCENTAJE DEL IVA DE LA SECUENCIA',
            'VALOR DE IVA DE LA SECUENCIA',
            'BASE DE RETENCIÓN',
            'BASE PARA CUENTAS MARCADAS COMO RETEIVA',
            'SECUENCIA GRAVADA O EXCENTA',
            'PORCENTAJE AIU',
            'BASE IVA AIU',
            'VALOR TOTAL IMPOCONSUMO DE LA SECUENCIA',
            'LÍNEA PRODUCTO',
            'GRUPO PRODUCTO',
            'CÓDIGO PRODUCTO',
            'CANTIDAD',
            'CANTIDAD DOS',
            'CÓDIGO DE LA BODEGA',
            'CÓDIGO DE LA UBICACIÓN',
            'CANTIDAD DE FACTOR DE CONVERSIÓN',
            'OPERADOR DE FACTOR DE CONVERSIÓN',
            'VALOR DEL FACTOR DE CONVERSIÓN',
            'GRUPO ACTIVOS',
            'CÓDIGO ACTIVO',
            'ADICIÓN O MEJORA',
            'VECES ADICIONALES A DEPRECIAR POR ADICIÓN O MEJORA',
            'VECES A DEPRECIAR NIIF',
            'NÚMERO DEL DOCUMENTO DEL PROVEEDOR',
            'PREFIJO DEL DOCUMENTO DEL PROVEEDOR',
            'AÑO DOCUMENTO DEL PROVEEDOR',
            'MES DOCUMENTO DEL PROVEEDOR',
            'DÍA DOCUMENTO DEL PROVEEDOR',
            'TIPO DOCUMENTO DE PEDIDO',
            'CÓDIGO COMPROBANTE DE PEDIDO',
            'NÚMERO DE COMPROBANTE PEDIDO',
            'SECUENCIA DE PEDIDO',
            'TIPO DE MONEDA ELABORACIÓN',
            'TIPO DE MONEDA ELABORACIÓN',
            'NÚMERO DE DOCUMENTO CRUCE',
            'NÚMERO DE VENCIMIENTO',
            'AÑO VENCIMIENTO DE DOCUMENTO CRUCE',
            'MES VENCIMIENTO DE DOCUMENTO CRUCE',
            'DÍA VENCIMIENTO DE DOCUMENTO CRUCE',
            'NÚMERO DE CAJA ASOCIADA AL COMPROBANTE',
            'DESCRIPCIÓN DE COMENTARIOS',
            'DESCRIPCIÓN LARGA',
            'INCONTERM',
            'DESCRIPCIÓN EXPORTACIÓN',
            'MEDIO DE TRANSPORTE',
            'PAÍS DE ORIGEN',
            'CIUDAD DE ORIGEN',
            'PAIS DESTINO',
            'CIUDAD DESTINO',
            'PESO NETO',
            'PESO BRUTO',
            'CONCEPTO DE FACTURACIÓN',
            'GRUPO DEL INMUEBLE',
            'SUBGRUPO DEL INMUEBLE',
            'NÚMERO DEL INMUEBLE',
            'CONCEPTO FACTURACION EN BLOQUE',
            'DATOS ESTABLECIMIENTO (L = LOCAL  O = OFICINA)',
            'NÚMERO ESTABLECIMIENTO',
        ];
    }

    public function columnFormats(): array
    {
        return [
            'F' => NumberFormat::FORMAT_NUMBER,
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
        return 'Resumen Siigo';
    }
}
