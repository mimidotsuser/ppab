<?php

namespace App\Models;

use App\Traits\AutofillAuthorFields;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Scout\Searchable;

class CustomerContract extends Model
{
    use HasFactory, AutofillAuthorFields, Searchable;


    /**
     * Items under this contract
     * @return BelongsToMany
     */
    public function productItems(): BelongsToMany
    {
        return $this->belongsToMany(ProductItem::class, 'customer_contract_items');
    }

    /**
     * Customer who owns of the contract
     * @return BelongsTo
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    /**
     * Tracking logs created by items under this log
     * @return HasMany
     */
    public function trackingLogs(): HasMany
    {
        return $this->hasMany(ProductItemActivity::class, 'customer_contract_id');
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

    public function toSearchableArray()
    {
        return [
            ''
        ];
    }
}
