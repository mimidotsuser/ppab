<?php

namespace App\Models;

use App\Traits\AutofillAuthorFields;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class MaterialRequisition extends Model
{
    use HasFactory, AutofillAuthorFields;

    protected $with = ['createdBy'];
    protected $hidden = ['email_thread_id'];

    /**
     * @return HasMany
     */
    public function items(): HasMany
    {
        return $this->hasMany(MaterialRequisitionItem::class,
            'material_requisition_id');
    }

    /**
     * @return HasMany
     */
    public function activities(): HasMany
    {
        return $this->hasMany(MaterialRequisitionActivity::class,
            'material_requisition_id');
    }

    public function latestActivity(): HasOne
    {
        return $this->hasOne(MaterialRequisitionActivity::class,
            'material_requisition_id')->latestOfMany();
    }

    public function allocationActivities(): MorphMany
    {
        return $this->morphMany(ProductItemActivity::class, 'eventable');
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
