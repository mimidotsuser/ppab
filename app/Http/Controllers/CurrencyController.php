<?php

namespace App\Http\Controllers;

use App\Models\Currency;

class CurrencyController extends Controller
{
    public function index()
    {
        return ['data' => Currency::get(['id', 'code', 'name'])];
    }
}
