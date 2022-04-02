<?php

namespace App\Models;

use App\Traits\AutofillAuthorFields;
use App\Traits\FilterScopes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InspectionNote extends Model
{
    use HasFactory, AutofillAuthorFields, FilterScopes;

    protected $with=['checklist'];

    /**
     * @return BelongsTo
     */
    public function goodsReceiptNote(): BelongsTo
    {
        return $this->belongsTo(GoodsReceiptNote::class, 'goods_receipt_note_id');
    }

    /**
     * @return HasMany
     */
    public function checklist(): HasMany
    {
        return $this->hasMany(InspectionChecklist::class, 'inspection_note_id');
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
