<?php

namespace App\Models;

use App\Traits\AutofillAuthorFields;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    use HasFactory, AutofillAuthorFields;

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_permissions');
    }
}
