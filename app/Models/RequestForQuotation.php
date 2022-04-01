<?php

namespace App\Models;

use App\Traits\AutofillAuthorFields;
use App\Traits\FilterScopes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RequestForQuotation extends Model
{
    use HasFactory, AutofillAuthorFields, FilterScopes;


    public function purchaseRequest(): BelongsTo
    {
        return $this->belongsTo(PurchaseRequest::class, 'purchase_request_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(RequestForQuotationItem::class,
            'request_for_quotation_id');
    }

    public function vendors(): BelongsToMany
    {
        return $this->belongsToMany(Vendor::class,'request_for_quotation_vendors',
            'request_for_quotation_id','vendor_id',);
    }

    /**
     * Author relationship
     * @return BelongsTo
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    /**
     * Editor relationship
     * @return BelongsTo
     */
    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by_id');
    }

}
