<?php

namespace App\Contracts;

use App\Models\Customer;
use App\Models\CustomerContract;
use App\Models\EntryRemark;
use App\Models\MaterialRequisition;
use App\Models\ProductItem;
use App\Models\ProductItemRepair;
use App\Models\ProductItemWarrant;
use App\Models\Warehouse;
use App\Models\Worksheet;

class ProductItemActivityContract
{

    public ProductItem $productItem;
    public Customer $customer;
    public Warehouse $warehouse;
    public EntryRemark $remark;
    public string $categoryCode;
    public string $categoryTitle;
    public string $covenant;
    public Worksheet|MaterialRequisition|CustomerContract|ProductItemWarrant $eventModel;
    public ProductItemRepair $repairModel;
}
