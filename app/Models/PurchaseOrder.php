<?php

namespace App\Models;

use App\Traits\AutofillAuthorFields;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseOrder extends Model
{
    use HasFactory, AutofillAuthorFields;

    protected $with = ['currency', 'vendor'];
    protected $casts=['doc_validity'=>'date'];

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function items()
    {
        return $this->hasMany(PurchaseOrderItem::class, 'purchase_order_id');
    }

    public function rfq(): BelongsTo
    {
        return $this->belongsTo(RequestForQuotation::class);
    }


    public function receivedItems()
    {
        return $this->hasManyThrough(GoodsReceiptNoteItem::class,
            GoodsReceiptNote::class, 'purchase_order_id',
            'goods_receipt_note_id', 'id', 'id');
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
