<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVendorRequest;
use App\Http\Requests\UpdateVendorRequest;
use App\Models\Vendor;
use App\Models\VendorContactPerson;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Response;

class VendorController extends Controller
{

    public function __construct()
    {
        $this->authorizeResource(Vendor::class, 'vendor');
    }

    /**
     * Display a listing of the resource.
     *
     * @return LengthAwarePaginator
     */
    public function index(): LengthAwarePaginator
    {
        $meta = $this->queryMeta(['created_at', 'name'], ['createdBy', 'contactPersons']);

        return Vendor::with($meta->include)
            ->when($meta, function ($query, $meta) {
                foreach ($meta->orderBy as $sortKey) {
                    $query->orderBy($sortKey, $meta->direction);
                }
            })
            ->paginate($meta->limit, '*', null, $meta->page);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreVendorRequest $request
     * @return Vendor[]
     */
    public function store(StoreVendorRequest $request)
    {

        $vendor = new Vendor;
        $vendor->name = $request->get('name');
        $vendor->street_address = $request->get('street_address');
        $vendor->telephone = $request->get('telephone');
        $vendor->email = $request->get('email');
        $vendor->mobile_phone = $request->get('mobile_phone');
        $vendor->postal_address = $request->get('postal_address');

        $vendor->save();

        if ($request->filled('contactPersons')) {
            $contacts = [];
            foreach ($request->get('contactPersons') as $row) {
                $contact = new VendorContactPerson;
                $contact->first_name = $row['first_name'];
                $contact->last_name = $row['last_name'];
                $contact->email = $row['email'];
                $contact->mobile_phone = $row['mobile_phone'];
                $contacts[] = $contact;
            }
            $vendor->contactPersons()->saveMany($contacts);
        }
        $vendor->refresh();
        $vendor->load('contactPersons');
        return ['data' => $vendor];
    }

    /**
     * Display the specified resource.
     *
     * @param Vendor $vendor
     * @return Vendor[]
     */
    public function show(Vendor $vendor): array
    {
        $meta = $this->queryMeta([], ['createdBy', 'contactPersons']);
        $vendor->load($meta->include);
        return ['data' => $vendor];
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateVendorRequest $request
     * @param Vendor $vendor
     * @return Vendor[]
     */
    public function update(UpdateVendorRequest $request, Vendor $vendor)
    {
        $vendor->name = $request->get('name') ?? $vendor->name;
        $vendor->street_address = $request->get('street_address');
        $vendor->telephone = $request->get('telephone');
        $vendor->email = $request->get('email');
        $vendor->mobile_phone = $request->get('mobile_phone');
        $vendor->postal_address = $request->get('postal_address');

        $vendor->update();

        if ($request->filled('contactPersons')) {
            $vendor->contactPersons()->delete();

            $contacts = [];
            foreach ($request->get('contactPersons') as $row) {
                $contact = new VendorContactPerson;
                $contact->first_name = $row['first_name'];
                $contact->last_name = $row['last_name'];
                $contact->email = $row['email'];
                $contact->mobile_phone = $row['mobile_phone'];
                $contacts[] = $contact;
            }

            $vendor->contactPersons()->saveMany($contacts);
        }
        $vendor->refresh();
        $vendor->load('contactPersons');
        return ['data' => $vendor];
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Vendor $vendor
     * @return Response
     */
    public function destroy(Vendor $vendor): Response
    {
        $vendor->delete();

        return response()->noContent();
    }
}
