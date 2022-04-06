<?php

namespace App\Models;

use App\Traits\AutofillAuthorFields;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;
use JetBrains\PhpStorm\ArrayShape;
use Laravel\Scout\Searchable;

class ProductItem extends Model
{
    use HasFactory, AutofillAuthorFields, Searchable;

    protected $casts = ['out_of_order' => 'boolean'];


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
    public function activities(): HasMany
    {
        return $this->hasMany(ProductItemActivity::class);
    }

    /**
     * The last tracking log entry
     * @return HasOne
     */
    public function latestActivity(): HasOne
    {
        return $this->hasOne(ProductItemActivity::class)->latestOfMany();
    }


    /**
     * The first tacking log entry
     * @return HasOne
     */
    public function oldestActivity(): HasOne
    {
        return $this->hasOne(ProductItemActivity::class)->oldestOfMany();
    }


    /**
     * All warrants ever created for this item
     * @return HasMany
     */
    public function warrants(): HasMany
    {
        return $this->hasMany(ProductItemWarrant::class, 'product_item_id');
    }

    public function activeWarrant()
    {
        return $this->hasOne(ProductItemWarrant::class)->latestOfMany()
            ->whereDate('warrant_start', '<=', Carbon::today())
            ->where(function ($query) {
                $query->orWhere('warrant_end', null);
                $query->orWhereDate('warrant_end', '<=', Carbon::today());
            });
    }

    /**
     * Last warrant ever created for this item
     * @return HasOne
     */
    public function lastWarrant(): HasOne
    {
        return $this->hasOne(ProductItemWarrant::class, 'product_item_id')
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

    public function lastContract()
    {
        return $this->contracts()->latest()->take(1);
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
