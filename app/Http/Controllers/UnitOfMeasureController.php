<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUnitOfMeasureRequest;
use App\Http\Requests\UpdateUnitsOfMeasureRequest;
use App\Models\UnitOfMeasure;
use Illuminate\Http\Response;

class UnitOfMeasureController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(UnitOfMeasure::class,'unit_of_measure');
    }

    /**
     * Display a listing of the resource.
     *
     * @return array
     */
    public function index(): array
    {
        return ['data' => UnitOfMeasure::all()];
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreUnitOfMeasureRequest $request
     * @return array
     */
    public function store(StoreUnitOfMeasureRequest $request): array
    {
        $uom = new UnitOfMeasure;
        $uom->code = $request->get('code');
        $uom->title = $request->get('title');
        $uom->unit = $request->get('unit');
        $uom->save();
        $uom->refresh();

        return ['data' => $uom];
    }

    /**
     * Display the specified resource.
     *
     * @param UnitOfMeasure $unitOfMeasure
     * @return array []
     */
    public function show(UnitOfMeasure $unitOfMeasure): array
    {
        return ['data' => $unitOfMeasure];
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateUnitsOfMeasureRequest $request
     * @param UnitOfMeasure $unitOfMeasure
     * @return UnitOfMeasure[]
     */
    public function update(UpdateUnitsOfMeasureRequest $request, UnitOfMeasure $unitOfMeasure)
    {
        $unitOfMeasure->code = $request->get('code') ?? $unitOfMeasure->code;
        $unitOfMeasure->title = $request->get('title') ?? $unitOfMeasure->title;
        $unitOfMeasure->unit = $request->get('unit') ?? $unitOfMeasure->unit;
        $unitOfMeasure->update();

        $unitOfMeasure->refresh();

        return ['data' => $unitOfMeasure];
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param UnitOfMeasure $unitOfMeasure
     * @return Response
     */
    public function destroy(UnitOfMeasure $unitOfMeasure)
    {
        $unitOfMeasure->delete();
        return \response()->noContent();
    }
}
