<?php

namespace App\Models;

use App\Traits\AutofillAuthorFields;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class GoodsReceiptNote extends Model
{
    use HasFactory, AutofillAuthorFields;

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(GoodsReceiptNoteItem::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(GoodsReceiptNoteActivity::class);
    }

    public function latestActivity(): HasOne
    {
        return $this->hasOne(GoodsReceiptNoteActivity::class)->latestOfMany();
    }

    public function inspectionNote(): HasOne
    {
        return $this->hasOne(InspectionNote::class, 'goods_receipt_note_id');
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
