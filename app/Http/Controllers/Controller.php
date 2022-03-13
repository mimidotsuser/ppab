<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * @param array $sortColumns
     * @param array $relationships
     * @param string $defaultSortColumn
     * @return object
     */
    public function queryMeta(array  $sortColumns = ['created_at'],
                              array  $relationships = ['createdBy'],
                              string $defaultSortColumn = 'created_at'): object
    {

        /* deserialize sorting columns and direction */
        $requestSortCols = explode(',', request('sort_by', $defaultSortColumn));

        /* deserialize columns to lazy load */
        $requestLazyLoad = explode(',', request('include', ''));

        return (object)[
            'include' => array_intersect($requestLazyLoad, $relationships),
            'orderBy' => array_intersect($requestSortCols, $sortColumns),
            'direction' => request('order', 'desc'),
            'limit' => request('limit', 15),
            'page' => request('page', 1)
        ];
    }
}
