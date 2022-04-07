<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use App\Models\Role;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use JetBrains\PhpStorm\ArrayShape;

class RoleController extends Controller
{

    public function __construct()
    {
        $this->authorizeResource(Role::class, 'role');
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return LengthAwarePaginator
     */
    public function index(Request $request): LengthAwarePaginator
    {
        $meta = $this->queryMeta(['created_at', 'name', 'description'],
            ['permissions', 'createdBy']);

        return Role::with($meta->include)
            ->when($request->search, function ($query, $searchTerm) {
                $query->where(function ($query) use ($searchTerm) {
                    $query->orWhereBeginsWith('name', $searchTerm);
                    $query->orWhereLike('name', $searchTerm);

                    $query->orWhereBeginsWith('description', $searchTerm);
                    $query->orWhereLike('description', $searchTerm);
                });
            })
            ->when($meta, function ($query, $meta) {
                foreach ($meta->orderBy as $sortKey) {
                    $query->orderBy($sortKey, $meta->direction);
                }
            })
            ->when($request->search)
            ->paginate($meta->limit, 'page', $meta->page);

    }


    /**
     * Store a newly created resource in storage.
     *
     * @param StoreRoleRequest $request
     * @return array
     */
    #[ArrayShape(['data' => "\App\Models\Role"])]
    public function store(StoreRoleRequest $request): array
    {
        DB::beginTransaction();

        $role = new Role;
        $role->name = $request->get('name');
        $role->description = $request->get('description');

        $role->save();

        $role->permissions()
            ->withPivot(['created_at', 'updated_at']) //force laravel to detect timestamps columns
            ->attach(Arr::flatten($request->get('permissions')),
                ['created_by_id' => Auth::id(), 'updated_by_id' => Auth::id()]);

        DB::commit();
        $role->refresh();
        $role->load(['permissions', 'createdBy']);
        return ['data' => $role];
    }

    /**
     * Display the specified resource.
     *
     * @param Role $role
     * @return array
     */
    #[ArrayShape(['data' => "\App\Models\Role"])]
    public function show(Role $role): array
    {
        $meta = $this->queryMeta([], ['permissions', 'createdBy']);
        $role->load($meta->include);

        return ['data' => $role];
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateRoleRequest $request
     * @param Role $role
     * @return array
     */
    #[ArrayShape(['data' => "\App\Models\Role"])]
    public function update(UpdateRoleRequest $request, Role $role): array
    {
        DB::beginTransaction();

        $role->name = $request->get('name') ?? $role->name;
        $role->description = $request->get('description') ?? $role->description;

        $role->update();

        if ($request->has('permissions')) {
            $role->permissions()
                ->syncWithPivotValues(Arr::flatten($request->get('permissions')),
                    ['created_by_id' => Auth::id(), 'updated_by_id' => Auth::id(),
                        'created_at' => Carbon::now(), 'updated_at' => Carbon::now()]);

        }

        DB::commit();

        $role->refresh();
        $role->load(['permissions', 'createdBy']);
        return ['data' => $role];
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Role $role
     * @return Response
     */
    public function destroy(Role $role): Response
    {
        $role->delete();

        return response()->noContent();
    }
}
