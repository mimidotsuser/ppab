<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use JetBrains\PhpStorm\ArrayShape;

class PermissionController extends Controller
{
    /**
     * Fetch all permissions
     *
     * @return array
     */
    #[ArrayShape(['data' => "mixed"])]
    public function index(): array
    {
        return ['data' => Permission::all()];
    }
}
