<?php

namespace App\Http\Controllers;

use App\Models\ProductCategory;
use JetBrains\PhpStorm\ArrayShape;

class ProductCategoryController extends Controller
{
    /**
     * Fetch all categories
     * @return array
     */
    #[ArrayShape(['data' => "mixed"])]
    public function index(): array
    {
        return ['data' => ProductCategory::all()];
    }
}
