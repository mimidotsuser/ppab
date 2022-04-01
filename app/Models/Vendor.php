<?php

namespace App\Models;

use App\Traits\AutofillAuthorFields;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Scout\Searchable;

class Vendor extends Model
{
    use HasFactory, AutofillAuthorFields, Searchable;

    /**
     * @return HasMany
     */
    public function contactPersons(): HasMany
    {
        return $this->hasMany(VendorContactPerson::class, 'vendor_id');
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
            'name' => $this->name,
            'address' => $this->address,
            'telephone' => $this->telephone,
            'email' => $this->email,
        ];
    }
}
