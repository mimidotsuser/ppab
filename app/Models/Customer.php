<?php

namespace App\Models;

use App\Traits\AutofillAuthorFields;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Laravel\Scout\Attributes\SearchUsingFullText;
use Laravel\Scout\Searchable;

class Customer extends Model
{
    use HasFactory, AutofillAuthorFields, Searchable;


    /**
     * @return MorphMany
     */
    public function productTrackingLogs(): MorphMany
    {
        return $this->morphMany(ProductItemActivity::class,
            'location', 'location_type', 'location_id');
    }

    /**
     * HO/Parent to the customer branch
     *
     * @return BelongsTo
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'parent_id');
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

    /**
     * @return array
     */
    #[SearchUsingFullText(['name', 'branch', 'region'])]
    public function toSearchableArray()
    {
        return [
            'name' => $this->name,
            'branch' => $this->branch,
            'region' => $this->region,
            'location' => $this->location,
        ];
    }
}
