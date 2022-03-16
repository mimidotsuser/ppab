<?php

namespace App\Providers;

use App\Models\Customer;
use App\Models\Warehouse;
use App\Models\Worksheet;
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
            'worksheet' => Worksheet::class
        ]);
    }
}
