<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCustomerContractRequest;
use App\Http\Requests\UpdateCustomerContractRequest;
use App\Models\CustomerContract;

class ClientContractController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreCustomerContractRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreCustomerContractRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\CustomerContract  $clientContract
     * @return \Illuminate\Http\Response
     */
    public function show(CustomerContract $clientContract)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateCustomerContractRequest  $request
     * @param  \App\Models\CustomerContract  $clientContract
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateCustomerContractRequest $request, CustomerContract $clientContract)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\CustomerContract  $clientContract
     * @return \Illuminate\Http\Response
     */
    public function destroy(CustomerContract $clientContract)
    {
        //
    }
}
