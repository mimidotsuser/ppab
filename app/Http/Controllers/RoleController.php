<?php

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use App\Models\Role;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use JetBrains\PhpStorm\ArrayShape;

class RoleController extends Controller
{

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function index(Request $request)
    {
        $meta = $this->queryMeta(['created_at', 'name', 'description'],
            ['permissions', 'createdBy']);

        return Role::search($request->search)
            ->query(function ($query) use ($meta) {
                foreach ($meta->orderBy as $sortKey) {
                    $query->orderBy($sortKey, $meta->direction);
                }
            })
            ->query(fn(Builder $query) => $query->with($meta->include))
            ->paginate($meta->limit, 'page', $meta->page);

    }


    /**
     * Store a newly created resource in storage.
     *
     * @param StoreRoleRequest $request
     * @return array
     */
    #[ArrayShape(['data' => "\App\Models\Role"])]
    public function store(StoreRoleRequest $request)
    {
        DB::beginTransaction();

        $role = new Role;
        $role->name = $request->get('name');
        $role->description = $request->get('description');

        $role->save();

        $role->permissions()->attach(Arr::flatten($request->get('permissions')),
            ['created_at' => Carbon::now(), 'updated_at' => Carbon::now()]);

        DB::commit();
        $role->refresh();
        $role->load('permissions');
        return ['data' => $role];
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\Role $role
     * @return array
     */
    public function show(Role $role)
    {
        return ['data' => $role];
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \App\Http\Requests\UpdateRoleRequest $request
     * @param \App\Models\Role $role
     * @return array
     */
    public function update(UpdateRoleRequest $request, Role $role)
    {
        DB::beginTransaction();

        $role->name = $request->get('name') ?? $role->name;
        $role->description = $request->get('description') ?? $role->description;

        $role->save();

        if ($request->has('permissions')) {
            $role->permissions()->sync(Arr::flatten($request->get('permissions')),
                ['created_at' => Carbon::now(), 'updated_at' => Carbon::now()]);
        }

        DB::commit();

        $role->refresh();
        $role->load('permissions');
        return ['data' => $role];
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\Role $role
     * @return \Illuminate\Http\Response
     */
    public function destroy(Role $role)
    {
        $role->delete();

        return response()->noContent();
    }
}
