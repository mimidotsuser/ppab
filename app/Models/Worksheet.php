<?php

namespace App\Models;

use App\Traits\AutofillAuthorFields;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Laravel\Scout\Searchable;

class Worksheet extends Model
{
    use HasFactory, AutofillAuthorFields, Searchable;


    /**
     * @return BelongsTo
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }


    /**
     * All product tracking logs related to a worksheet entry
     * @return MorphMany
     */
    public function entries(): MorphMany
    {
        return $this->morphMany(ProductItemActivity::class, 'eventable');

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
            'sn' => $this->sn,
            'reference' => $this->reference
        ];
    }
}
