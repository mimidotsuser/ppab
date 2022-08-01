<?php

namespace App\Models;

use App\Traits\AutofillAuthorFields;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaterialRequisitionItem extends Model
{
    use HasFactory, AutofillAuthorFields;

    protected $with = ['customer', 'product']; //always eager load

    protected $casts = [
        'requested_qty' => 'integer',
        'approved_qty'=>'integer',
        'issued_qty'=>'integer',
        'verified_qty'=>'integer',
    ];

    public function request(): BelongsTo
    {
        return $this->belongsTo(MaterialRequisition::class, 'material_requisition_id');
    }


    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function worksheet(): BelongsTo
    {
        return $this->belongsTo(Worksheet::class);
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
