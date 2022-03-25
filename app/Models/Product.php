<?php

namespace App\Models;

use App\Traits\AutofillAuthorFields;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Laravel\Scout\Attributes\SearchUsingFullText;
use Laravel\Scout\Searchable;

class Product extends Model
{
    use HasFactory, AutofillAuthorFields, Searchable;


    public function parent()
    {
        return $this->belongsTo(Product::class, 'parent_id');
    }


    /**
     * @return BelongsTo
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'product_category_id');
    }

    public function balance()
    {
        return $this->hasOne(StockBalance::class);
    }

    public function aggregateBalance()
    {
        $exp = <<< EOD
            `internal_code`,sum(`stock_balance`) as stock_balance,
            sum(`virtual_balance`) as virtual_balance,
            max(reorder_level) as reorder_level
        EOD;

        return $this->hasMany(StockBalance::class, 'internal_code', 'internal_code')
            ->selectRaw($exp)
            ->groupBy('internal_code');
    }

    /**
     * @param $query
     * @return mixed
     */
    public function scopeIsSpare($query): mixed
    {
        return $query->category->where('name', 'spare');
    }

    /**
     * @param $query
     * @return mixed
     */
    public function scopeIsMachine($query)
    {
        return $query->category->where('name', 'machine');
    }

    public function scopeWhereLike($query, $column, $value)
    {
        return $query->where($column, 'like', '%' . $value . '%');
    }

    public function scopeOrWhereLike($query, $column, $value, $startBoundary = true)
    {
        return $query->orWhere($column, 'like', $startBoundary ? '%' : '' . $value . '%');
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
    #[SearchUsingFullText(['item_code', 'description', 'local_description'])]
    public function toSearchableArray(): array
    {
        return [
            'item_code' => $this->item_code,
            'manufacturer_part_number' => $this->manufacturer_part_number,
            'description' => $this->description,
            'local_description' => $this->local_description,
            'chinese_description' => $this->chinese_description,
            'economic_order_qty' => $this->economic_order_qty,
            'min_level' => $this->min_level,
            'reorder_level' => $this->reorder_level,
            'max_level' => $this->max_level,
        ];
    }
}
