<?php

namespace App\Models;

use App\Traits\AutofillAuthorFields;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseRequest extends Model
{
    use HasFactory, AutofillAuthorFields;

    protected $hidden = ['email_thread_id'];


    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function activities()
    {
        return $this->hasMany(PurchaseRequestActivity::class);
    }

    public function latestActivity()
    {
        return $this->hasOne(PurchaseRequestActivity::class)->latestOfMany();
    }

    public function items()
    {
        return $this->hasMany(PurchaseRequestItem::class);
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
