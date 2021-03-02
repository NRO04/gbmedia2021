<?php

namespace App\Exports\Boutique;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class Inventory implements WithMultipleSheets
{
	use Exportable;

    private $products;
    private $locations;
    private $date;

    public function __construct($products, $locations, $date)
    {
        $this->products = $products;
        $this->locations = $locations;
        $this->date = $date;
    }

    public function sheets(): array
    {
        $sheets = [];

        $sheets[] = new InventorySheet($this->products, $this->locations, $this->date);

        return $sheets;
    }
}
