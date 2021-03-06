<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use JetBrains\PhpStorm\ArrayShape;

class ProductController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Product::class, 'product');
    }

    /**
     * Display a listing of any product.
     *
     * @return LengthAwarePaginator
     */
    public function index(Request $request): LengthAwarePaginator
    {
        $meta = $this->queryMeta(['created_at', 'item_code', 'economic_order_qty', 'min_level',
            'reorder_level', 'max_level'], ['createdBy', 'updatedBy', 'parent','balance','variants']);

        array_push($meta->include, 'category'); //category relationship load always

        return Product::with($meta->include)
            ->when($request->search, function ($query, $searchTerm) {
                $query->where(function ($query) use ($searchTerm) {
                    $query->orWhereBeginsWith('item_code', $searchTerm,);
                    $query->orWhereLike('item_code', $searchTerm,);

                    $query->orWhereBeginsWith('description', $searchTerm);
                    $query->orWhereLike('description', $searchTerm);

                    $query->orWhereBeginsWith('local_description', $searchTerm);
                    $query->orWhereLike('local_description', $searchTerm);
                });

            })->when($meta, function ($query, $meta) {
                foreach ($meta->orderBy as $sortKey) {
                    $query->orderBy($sortKey, $meta->direction);
                }
            })
            ->when(!$request->get('variants'), function ($query) {
                $query->whereNull('variant_of_id');
            })
            ->paginate($meta->limit, '*', null, $meta->page);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreProductRequest $request
     * @return Product[]
     */
    #[ArrayShape(['data' => "\App\Models\Product"])]
    public function store(StoreProductRequest $request): array
    {
        DB::beginTransaction();
        $product = new Product;
        $product->internal_code = Str::lower(Str::random(12));
        $product->parent_id = $request->get('parent_id');
        $product->product_category_id = $request->get('product_category_id');
        $product->item_code = $request->get('item_code');
        $product->manufacturer_part_number = $request->get('manufacturer_part_number');
        $product->description = $request->get('description');
        $product->local_description = $request->get('local_description');
        $product->chinese_description = $request->get('chinese_description');
        $product->economic_order_qty = $request->get('economic_order_qty');
        $product->min_level = $request->get('min_level');
        $product->reorder_level = $request->get('reorder_level');
        $product->max_level = $request->get('max_level');
        $product->save();

        $product->refresh();

        if ($request->get('create_old_variant') === true) {
            //clone and create new record
            $variant = $product->replicate(['id']);
            $variant->variant_of_id = $product->id;
            $variant->item_code = $variant->item_code . '[old]';
            $variant->economic_order_qty = 0;
            $variant->min_level = 0;
            $variant->reorder_level = 0;
            $variant->max_level = 0;
            $variant->save();
        }
        DB::commit();

        $product->load('category');

        return ['data' => $product];
    }

    /**
     * Display the specified resource.
     *
     * @param Product $product
     * @return Product[]
     */
    #[ArrayShape(['data' => "\App\Models\Product"])]
    public function show(Product $product): array
    {
        $product->load(['category', 'createdBy']);

        return ['data' => $product];
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateProductRequest $request
     * @param Product $product
     * @return Product[]
     */
    #[ArrayShape(['data' => "\App\Models\Product"])]
    public function update(UpdateProductRequest $request, Product $product): array
    {
        $product->parent_id = $request->get('parent_id') ?? $product->parent_id;
        $product->product_category_id = $request->get('product_category_id') ??
            $product->product_category_id;
        $product->item_code = $request->get('item_code') ?? $product->item_code;
        $product->manufacturer_part_number = $request->get('manufacturer_part_number') ??
            $product->manufacturer_part_number;
        $product->description = $request->get('description') ?? $product->description;
        $product->local_description = $request->get('local_description') ??
            $product->local_description;
        $product->chinese_description = $request->get('chinese_description') ??
            $product->chinese_description;
        $product->economic_order_qty = $request->get('economic_order_qty') ??
            $product->economic_order_qty;
        $product->min_level = $request->get('min_level') ?? $product->min_level;
        $product->reorder_level = $request->get('reorder_level') ?? $product->reorder_level;
        $product->max_level = $request->get('max_level') ?? $product->max_level;
        $product->update();

        $product->refresh();

        $product->load('category');

        return ['data' => $product];
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Product $product
     * @return Response
     */
    public function destroy(Product $product): Response
    {
        $product->delete();
        return response()->noContent();
    }
}
