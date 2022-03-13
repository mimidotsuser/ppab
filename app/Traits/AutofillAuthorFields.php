<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;

trait AutofillAuthorFields
{
    static function bootAutofillAuthorFields()
    {

        static::creating(function ($model) {
            $model->created_by_id = Auth::id();
            $model->updated_by_id = Auth::id();
        });

        static::updating(function ($model) {
            $model->updated_by_id = Auth::id();
        });

        static::saving(function ($model) {
            $model->updated_by_id = Auth::id();
        });
    }

}
