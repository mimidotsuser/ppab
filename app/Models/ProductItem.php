<?php

namespace App\Models;

use App\Traits\AutofillAuthorFields;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use JetBrains\PhpStorm\ArrayShape;
use Laravel\Scout\Searchable;

class ProductItem extends Model
{
    use HasFactory, AutofillAuthorFields, Searchable;

    protected $table = 'product_items';


    /**
     *  Kind of product
     * @return BelongsTo
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * All logs pertaining this product
     * @return HasMany
     */
    public function entryLogs(): HasMany
    {
        return $this->hasMany(ProductTrackingLog::class, 'product_item_id');
    }

    /**
     * The last tracking log entry
     * @return HasOne
     */
    public function latestEntryLog(): HasOne
    {
        return $this->hasOne(ProductTrackingLog::class, 'product_item_id')
            ->latestOfMany();
    }


    /**
     * The first tacking log entry
     * @return HasOne
     */
    public function oldestEntryLog(): HasOne
    {
        return $this->hasOne(ProductTrackingLog::class, 'product_item_id')
            ->oldestOfMany();
    }


    /**
     * All warrants ever created for this item
     * @return HasMany
     */
    public function warrants(): HasMany
    {
        return $this->hasMany(ProductWarrant::class, 'product_item_id');
    }

    /**
     * Last warrant ever created for this item
     * @return HasOne
     */
    public function lastWarrant(): HasOne
    {
        return $this->hasOne(ProductWarrant::class, 'product_item_id')
            ->latestOfMany();
    }

    /**
     * Contracts ever created for this item
     * @return BelongsToMany
     */
    public function contracts(): BelongsToMany
    {
        return $this->belongsToMany(CustomerContract::class, 'customer_contract_items');
    }

    public function scopeLastContract()
    {
        return $this->contracts()->latest()->first();

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


    public function scopeWhereLike($query, $column, $value)
    {
        return $query->where($column, 'like', '%' . $value . '%');
    }

    public function scopeOrWhereLike($query, $column, $value)
    {
        return $query->orWhere($column, 'like', '%' . $value . '%');
    }

    /**
     * @return array
     */
    #[ArrayShape(['serial_number' => "mixed", 'sn' => "mixed"])]
    public function toSearchableArray(): array
    {
        return [
            'serial_number' => $this->serial_number,
            'sn' => $this->sn,
        ];
    }
}
