<?php

namespace App\Providers;

use App\Events\B2CRequestQtyModified;
use App\Events\ProductCheckout;
use App\Events\ProductItemUpsert;
use App\Listeners\UpdateB2CPipelineBalance;
use App\Listeners\UpdateStockInBalance;
use App\Listeners\UpdateStockOutBalance;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        ProductItemUpsert::class => [
            UpdateStockInBalance::class
        ],
        B2CRequestQtyModified::class => [
            UpdateB2CPipelineBalance::class
        ],
        ProductCheckout::class => [
            UpdateStockOutBalance::class
        ]
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
