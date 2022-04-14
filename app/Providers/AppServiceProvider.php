<?php

namespace App\Providers;

use App\Mixins\ModelFilterMixin;
use App\Models\Customer;
use App\Models\CustomerContract;
use App\Models\MaterialRequisition;
use App\Models\ProductItemWarrant;
use App\Models\Warehouse;
use App\Models\Worksheet;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Relation::morphMap([
            'warehouse' => Warehouse::class,
            'customer' => Customer::class,
            'worksheet' => Worksheet::class,
            'material_requisition' => MaterialRequisition::class,
            'customer_contract' => CustomerContract::class,
            'product_item_warrant' => ProductItemWarrant::class,
        ]);

        Builder::mixin(new ModelFilterMixin());
    }
}
