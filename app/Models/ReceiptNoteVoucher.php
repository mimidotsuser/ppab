<?php

namespace App\Models;

use App\Traits\AutofillAuthorFields;
use App\Traits\FilterScopes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ReceiptNoteVoucher extends Model
{
    use HasFactory, AutofillAuthorFields, FilterScopes;

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(ReceiptNoteVoucherItem::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(ReceiptNoteVoucherActivity::class);
    }

    public function latestActivity(): HasOne
    {
        return $this->hasOne(ReceiptNoteVoucherActivity::class)->latestOfMany();
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
