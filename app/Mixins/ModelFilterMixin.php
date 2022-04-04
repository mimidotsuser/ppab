<?php

namespace App\Mixins;

/**
 * @method where($column, string $string, string $string1)
 * @method orWhere($column, string $string, string $string1)
 * @method whereRelation(string $relation, $column, string $string, string $string1)
 * @method orWhereRelation(string $relation, $column, string $string, string $string1)
 */
class ModelFilterMixin
{

    public function whereLike()
    {
        return fn($column, $value) => $this->where($column, 'like', '%' . $value . '%');
    }

    public function orWhereLike()
    {
        return fn($column, $value) => $this->orWhere($column, 'like', '%' . $value . '%');
    }

    public function whereBeginsWith()
    {
        return fn($column, $value) => $this->where($column, 'like', $value . '%');
    }

    public function orWhereBeginsWith()
    {
        return fn($column, $value) => $this->orWhere($column, 'like', $value . '%');
    }

    public function whereEndsWith()
    {
        return fn($column, $value) => $this->where($column, 'like', '%' . $value);
    }

    public function orWhereEndsWith()
    {
        return fn($column, $value) => $this->orWhere($column, 'like', '%' . $value);
    }

    public function whereRelationLike()
    {
        return function (string $relation, $column, $value) {
            return $this->whereRelation($relation, $column, 'like', '%' . $value . '%');
        };
    }

    public function orWhereRelationLike()
    {
        return function (string $relation, $column, $value) {
            return $this->orWhereRelation($relation, $column, 'like', '%' . $value . '%');
        };
    }

    public function whereRelationBeginsWith()
    {
        return function (string $relation, $column, $value) {
            return $this->whereRelation($relation, $column, 'like', $value . '%');
        };
    }

    public function orWhereRelationBeginsWith()
    {
        return function (string $relation, $column, $value) {
            return $this->orWhereRelation($relation, $column, 'like', $value . '%');
        };

    }

    public function whereRelationEndsWith()
    {
        return function (string $relation, $column, $value) {
            return $this->whereRelation($relation, $column, 'like', '%' . $value);
        };
    }

    public function orWhereRelationEndsWith()
    {
        return function (string $relation, $column, $value) {
            return $this->orWhereRelation($relation, $column, 'like', '%' . $value);
        };
    }
}
