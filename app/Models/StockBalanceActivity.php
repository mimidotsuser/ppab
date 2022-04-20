<?php

namespace App\Models;

use App\Traits\AutofillAuthorFields;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class StockBalanceActivity extends Model
{
    use HasFactory, AutofillAuthorFields;

    public function balance(): BelongsTo
    {
        return $this->belongsTo(StockBalance::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class,);
    }

    public function event(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'event_type', 'event_id');
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
