<?php

namespace App\Services;

use App\Contracts\ProductItemActivityContract;
use App\Models\Customer;
use App\Models\CustomerContract;
use App\Models\ProductItem;
use App\Models\ProductItemActivity;
use App\Models\ProductItemWarrant;

class ProductItemService
{

    /**
     * @param ProductItemActivityContract $productItemActivityContract
     * @return ProductItemActivity
     */
    public function serializeActivity(ProductItemActivityContract $productItemActivityContract): ProductItemActivity
    {

        $activity = new ProductItemActivity;

        $activity->covenant = $productItemActivityContract->covenant;
        $activity->log_category_code = $productItemActivityContract->categoryCode;
        $activity->log_category_title = $productItemActivityContract->categoryTitle;
        $activity->productItem()->associate($productItemActivityContract->productItem);

        if (!empty($productItemActivityContract->customer)) {
            $activity->location()->associate($productItemActivityContract->customer);

            //set any active warrant
            $warrant = $this->activeWarrant($productItemActivityContract->productItem,
                $productItemActivityContract->customer);
            if (isset($warrant)) {
                $activity->warrant()->associate($warrant);
            }
            //set any active contract
            $contract = $this->activeContract($productItemActivityContract->productItem,
                $productItemActivityContract->customer);
            if (isset($contract)) {
                $activity->contract()->associate($warrant);
            }

        } elseif (!empty($productItemActivityContract->warehouse)) {
            $activity->location()->associate($productItemActivityContract->warehouse);
        }

        if (isset( $productItemActivityContract->eventModel)) {
            $activity->eventable()->associate($productItemActivityContract->eventModel);
        }

        if (isset($productItemActivityContract->repairModel)) {
            $activity->repair()->associate($productItemActivityContract->repairModel);
        }

        return $activity;
    }


    public function activeWarrant(ProductItem $productItem, Customer $customer): ProductItemWarrant|null
    {
        $lastWarranty = ProductItemWarrant::latest()
            ->where('customer_id', $customer->id)
            ->where('product_item_id', $productItem->id)
            ->first();

        if (empty($lastWarranty)) {
            return null;
        }

        /*check if warranty is active i.e.
            - start date is today or before today
            - end date is either null or is either today or past today
       */
        if ($lastWarranty->warrant_start->isPast() &&
            (is_null($lastWarranty->warrant_end) || $lastWarranty->warrant_end->addDay()->isFuture())) {
            return $lastWarranty;
        }
        return null;
    }

    public function activeContract(ProductItem $productItem, Customer $customer): null|CustomerContract
    {
        /**
         * Checking on last product item activity may not work if the
         * contract was to start at a later date
         */
        $lastContract = CustomerContract::where('customer_id', $customer->id)
            ->latest()
            ->whereRelation('productItems', 'product_item_id', $productItem->id)
            ->first();

        if (empty($lastContract) || $lastContract->active === false) {
            return null;
        }
        /**
         * Check if the contract is active
         */
        if ($lastContract->start_date->isPast() &&
            (is_null($lastContract->expiry_date) || $lastContract->expiry_date->addDay()->isFuture())) {
            return $lastContract;
        }
        return null;
    }
}
