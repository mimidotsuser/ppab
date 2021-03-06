<?php

namespace App\Models;

use App\Traits\AutofillAuthorFields;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Laravel\Scout\Searchable;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, Searchable;
    use AutofillAuthorFields;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'email_verified_at'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function role()
    {
        return $this->belongsTo(Role::class);
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

    public function scopeMRFVerifier($query)
    {
        return $query->whereRelation('role.permissions', 'name', 'materialRequisition.verify');
    }

    public function scopeMRFApprover($query)
    {
        return $query->whereRelation('role.permissions', 'name', 'materialRequisition.approve');
    }

    public function scopeMRFIssuer($query)
    {
        return $query->whereRelation('role.permissions', 'name', 'checkout.create');
    }

    public function scopePurchaseRequestVerifier($query)
    {
        return $query->whereRelation('role.permissions', 'name', 'purchaseRequests.verify');
    }

    public function scopePurchaseRequestApprover($query)
    {
        return $query->whereRelation('role.permissions', 'name', 'purchaseRequests.approve');
    }

    public function scopeGoodsReceivedNoteInspector($query)
    {
        return $query->whereRelation('role.permissions', 'name', 'inspectionNote.create');
    }

    public function scopeGoodsReceivedNoteApprover($query)
    {
        return $query->whereRelation('role.permissions', 'name', 'goodsReceiptNote.approve');
    }

    public function toSearchableArray()
    {
        return [
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email
        ];
    }
}
