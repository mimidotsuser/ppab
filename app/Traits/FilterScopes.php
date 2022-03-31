<?php

namespace App\Traits;

trait FilterScopes
{

    public function scopeWhereLike($query, $column, $value)
    {
        return $query->where($column, 'like', '%' . $value . '%');
    }

    public function scopeOrWhereLike($query, $column, $value,)
    {
        return $query->orWhere($column, 'like', '%' . $value . '%');
    }

    public function scopeWhereBeginsWith($query, $column, $value,)
    {
        return $query->where($column, 'like', $value . '%');
    }

    public function scopeOrWhereBeginsWith($query, $column, $value,)
    {
        return $query->orWhere($column, 'like', $value . '%');
    }

    public function scopeWhereEndsWith($query, $column, $value,)
    {
        return $query->where($column, 'like', '%' . $value);
    }

    public function scopeOrWhereEndsWith($query, $column, $value,)
    {
        return $query->orWhere($column, 'like', '%' . $value);
    }
}