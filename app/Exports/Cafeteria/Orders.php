<?php

namespace App\Exports\Cafeteria;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class Orders implements WithMultipleSheets
{
	use Exportable;

    private $location_orders;
    private $menu_description;
    private $menu_type;

    public function __construct($location_orders = null, $menu_type = null, $menu_description = null)
    {
        $this->location_orders = $location_orders;
        $this->menu_description = $menu_description;
        $this->menu_type = $menu_type;
    }

    public function sheets(): array
    {
        $sheets = [];

        foreach($this->location_orders AS $location => $orders) {
            $sheets[] = new LocationSheet($location, $this->menu_type, $this->menu_description, $orders);
        }

        return $sheets;
    }
}
