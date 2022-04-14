<?php

namespace App\Contracts;

use App\Models\Product;

class RepairProductContract
{

    public Product $product;
    public int $old_total = 0;
    public int $new_total = 0;
}
