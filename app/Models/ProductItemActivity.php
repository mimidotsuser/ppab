<?php

namespace App\Models;

use App\Traits\AutofillAuthorFields;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Laravel\Scout\Searchable;

class ProductItemActivity extends Model
{
    use HasFactory, AutofillAuthorFields, Searchable;


    /**
     * Contract when this log was created
     * @return
     */
    public function contract()
    {
        return $this->belongsTo(CustomerContract::class, 'customer_contract_id');
    }

    /**
     * Item for this log
     * @return BelongsTo
     */
    public function productItem(): BelongsTo
    {
        return $this->belongsTo(ProductItem::class, 'product_item_id');
    }

    /**
     * Warrant under which this log was created with
     * @return BelongsTo
     */
    public function warrant(): BelongsTo
    {
        return $this->belongsTo(ProductItemWarrant::class, 'product_item_warrant_id');
    }

    /**
     * Work done description
     *
     * @return BelongsTo
     */
    public function remark(): BelongsTo
    {
        return $this->belongsTo(EntryRemark::class, 'entry_remark_id');
    }

    /**
     * location of the item when this log was created. Can be warehouse or on customer premise
     * @return MorphTo
     */
    public function location(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'location_type', 'location_id');
    }

    /**
     * The process event that triggered the log e.g. worksheet, checkout request (Can be null)
     * @return MorphTo
     */
    public function eventable()
    {
        return $this->morphTo(__FUNCTION__, 'eventable_type', 'eventable_id');
    }


    /**
     * Holds the repair items group used
     * @return BelongsTo
     */
    public function repair(): BelongsTo
    {
        return $this->belongsTo(ProductItemRepair::class, 'product_item_repair_id');
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

        ];
    }
}
