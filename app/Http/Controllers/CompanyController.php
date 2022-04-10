<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCompanyRequest;
use App\Http\Requests\UpdateCompanyRequest;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CompanyController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Company::class, 'company');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function index()
    {
        $meta = $this->queryMeta([], ['updatedBy', 'createdBy']);

        return Company::with($meta->include)
            ->paginate($meta->limit, '*', null, $meta->page);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \App\Http\Requests\StoreCompanyRequest $request
     * @return Company[]
     */
    public function store(StoreCompanyRequest $request)
    {
        $company = new Company;
        $company->name = $request->get('name');
        $company->logo_url = $request->get('logo_url');
        $company->street_address = $request->get('street_address');
        $company->postal_address = $request->get('postal_address');
        $company->telephone = $request->get('telephone');
        $company->mobile_phone = $request->get('mobile_phone');
        $company->website = $request->get('website');
        $company->save();
        return ['data' => $company];
    }

    /**
     * Display the specified resource.
     *
     * @param Company $company
     * @return Company[]
     */
    public function show(Company $company)
    {
        $meta = $this->queryMeta([], ['updatedBy', 'createdBy']);

        $company->load($meta->include);
        return ['data' => $company];
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \App\Http\Requests\UpdateCompanyRequest $request
     * @param Company $company
     * @return Company[]
     */
    public function update(UpdateCompanyRequest $request, Company $company)
    {
        $company->name = $request->get('name', $company->name);
        $company->street_address = $request->get('street_address');
        $company->logo_url = $request->get('logo_url');
        $company->postal_address = $request->get('postal_address');
        $company->telephone = $request->get('telephone');
        $company->mobile_phone = $request->get('mobile_phone');
        $company->website = $request->get('website');

        $company->update();

        return ['data' => $company];
    }


    public function uploadLogo(Request $request, Company $company)
    {
        $request->validate(['logo' => ['file', 'image', 'max:5000']]);
        $company->logo_url = $request->file('logo')->storePublicly('public');
        $company->update();
        return ['data' => $company];
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Company $company
     * @return Response
     */
    public function destroy(Company $company)
    {
        $company->delete();
        return response()->noContent();
    }
}
