<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use Illuminate\Database\Eloquent\Collection;

class PermissionController extends Controller
{
    /**
     * Fetch all permissions
     *
     * @return Collection
     */
    public function index(): Collection
    {
        return Permission::all();
    }
}
