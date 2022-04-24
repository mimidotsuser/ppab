<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductItemActivity;
use App\Models\StockBalance;
use App\Models\User;
use App\Models\Worksheet;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    /**
     * @param Request $request
     * @return array
     */
    public function worksheetsCountByCustomer(Request $request): array
    {
        $from = Carbon::now()->subMonths(3);
        $to = Carbon::now();

        if ($request->filled('between')) {
            $between = explode(',', $request->get('between'));
            if (!empty($between[0]) && (bool)strtotime($between[0])) {
                $from = $between[0];
            }

            if (!empty($between[1]) && (bool)strtotime($between[1])) {
                $to = $between[1];
            }
        }

        $data = Worksheet::query()
            ->select([
                Worksheet::query()->from . '.customer_id',
                DB::raw('DATE(`' . Worksheet::query()->from . '`.`created_at`) AS `created_at`'),
                DB::raw('COUNT(`' . Worksheet::query()->from . '`.`id`) AS `total`'),
                Customer::query()->from . '.name',
                Customer::query()->from . '.branch',
                Customer::query()->from . '.region',
            ])
            ->when(!$request->get('customerIds'), function (Builder $builder) {
                $builder->from(function (QueryBuilder $query) {
                    $query->selectRaw('COUNT(id) as toto,customer_id')
                        ->from(Worksheet::query()->from)
                        ->groupBy('customer_id')
                        ->orderByDesc('toto')
                        ->limit(10); //top 10
                }, 'worksheet_temp')
                    ->leftJoin(Worksheet::query()->from, 'worksheet_temp.customer_id', '=', Worksheet::query()->from . '.customer_id');
            })
            ->whereBetween(Worksheet::query()->from . '.created_at', [$from, $to])
            ->when($request->get('customerIds'), function (Builder $builder, $customerIds) {
                $builder->whereIn('customer_id', explode(',', $customerIds));
            })
            ->leftJoin(Customer::query()->from, Worksheet::query()->from . '.customer_id', '=', Customer::query()->from . '.id')
            ->groupBy([
                Worksheet::query()->from . '.customer_id',
                DB::raw('DATE(`' . Worksheet::query()->from . '`.`created_at`)')
            ])
            ->orderBy('created_at')
            ->get();

        return ['data' => $data];
    }

    /**
     * @param Request $request
     * @return array
     */
    public function worksheetsCountByAuthor(Request $request): array
    {
        $from = Carbon::now()->subMonths(6);
        $to = Carbon::now();

        if ($request->filled('between')) {
            $between = explode(',', $request->get('between'));
            if (!empty($between[0]) && (bool)strtotime($between[0])) {
                $from = $between[0];
            }

            if (!empty($between[1]) && (bool)strtotime($between[1])) {
                $to = $between[1];
            }
        }

        $data = Worksheet::query()
            ->select([
                DB::raw('COUNT(`' . Worksheet::query()->from . '`.`id`) AS `total`'),
                DB::raw('CONCAT(`'.User::query()->from .'`.`first_name`," ",`'. User::query()->from.'`.`last_name`) AS `name`')
            ])
            ->when(!$request->get('createdByIds'), function (Builder $builder) {
                $builder->from(function (QueryBuilder $query) {
                    $query->selectRaw('COUNT(id) as toto,created_by_id')
                        ->from(Worksheet::query()->from)
                        ->groupBy('created_by_id')
                        ->orderByDesc('toto')
                        ->limit(10); //top 10
                }, 'worksheet_temp')
                    ->leftJoin(Worksheet::query()->from, 'worksheet_temp.created_by_id', '=', Worksheet::query()->from . '.created_by_id');
            })
            ->whereBetween(Worksheet::query()->from . '.created_at', [$from, $to])
            ->when($request->get('createdByIds'), function (Builder $builder, $authorsIds) {
                $builder->whereIn('created_by_id', explode(',', $authorsIds));
            })
            ->leftJoin(User::query()->from, Worksheet::query()->from . '.created_by_id', '=', User::query()->from . '.id')
            ->groupBy([Worksheet::query()->from . '.created_by_id'])
            ->get();

        return ['data' => $data];
    }

    /**
     *
     * @return array
     */
    public function productsCountByCategory(): array
    {
        $data = Product::query()
            ->select([
                DB::raw('COUNT(`' . Product::query()->from . '`.`product_category_id`) AS `total`'),
                DB::raw('`' . Product::query()->from . '`.`product_category_id` AS `category_id`'),
                ProductCategory::query()->from . '.name'
            ])
            ->whereNull('variant_of_id')
            ->leftJoin(ProductCategory::query()->from, Product::query()->from . '.product_category_id', '=', ProductCategory::query()->from . '.id')
            ->groupBy(['product_category_id'])
            ->get();

        return ['data' => $data];
    }

    /**
     * @return array
     */
    public function productsOutOfStockCount(): array
    {
        $data = Product::query()
            ->select([
                DB::raw('COUNT(`' . Product::query()->from . '`.`product_category_id`) AS `total`'),
                DB::raw('`' . Product::query()->from . '`.`product_category_id` AS `category_id`'),
                ProductCategory::query()->from . '.name'
            ])
            ->leftJoin(ProductCategory::query()->from, Product::query()->from . '.product_category_id', '=', ProductCategory::query()->from . '.id')
            ->leftJoin(StockBalance::query()->from, Product::query()->from . '.id', '=', StockBalance::query()->from . '.product_id')
            ->where(StockBalance::query()->from . '.out_of_stock', true)
            ->groupBy(['product_category_id'])
            ->get();

        return ['data' => $data];
    }

    public function productItemsCountByLocation(): array
    {
        $data = ProductItemActivity::query()
            ->select([
                DB::raw('COUNT(`' . ProductItemActivity::query()->from . '`.`location_type`) AS `total`'),
                DB::raw('`' . ProductItemActivity::query()->from . '`.`location_type` as `location`')
            ])
            ->from(function (QueryBuilder $builder) {
                $builder->selectRaw('MAX(id) as id')
                    ->from(ProductItemActivity::query()->from)
                    ->groupBy('product_item_id');
            }, 'latest_activity')
            ->leftJoin(ProductItemActivity::query()->from, 'latest_activity.id', '=', ProductItemActivity::query()->from . '.id')
            ->groupBy(['location_type'])
            ->get();

        return ['data' => $data];
    }
}
