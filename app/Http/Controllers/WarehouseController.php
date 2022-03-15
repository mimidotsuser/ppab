<?php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use JetBrains\PhpStorm\ArrayShape;

class WarehouseController extends Controller
{

    /**
     * Fetch all warehouses
     * @return array
     */
    #[ArrayShape(['data' => "mixed"])]
    public function index(): array
    {
        return ['data' => Warehouse::all()];
    }
}
