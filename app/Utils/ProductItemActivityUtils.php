<?php

namespace App\Utils;

use JetBrains\PhpStorm\ArrayShape;

abstract class ProductItemActivityUtils
{

    /**
     * @return string[]
     */

    #[ArrayShape(['INITIAL_ENTRY' => "string", 'CUSTOMER_TO_CUSTOMER_TRANSFER' => "string",
        'CUSTOMER_TO_WAREHOUSE_TRANSFER' => "string", 'WARRANTY_UPDATE' => "string",
        'MATERIAL_REQUISITION_ISSUED' => "string", 'WAREHOUSE_TO_WAREHOUSE_TRANSFER' => "string",
        'CONTRACT_CREATED'=>"string",'CONTRACT_UPDATED'=>"string"])]
    public static function activityCategoryCodes(): array
    {
        return [
            'INITIAL_ENTRY' => 'INITIAL_ENTRY',
            'CUSTOMER_TO_CUSTOMER_TRANSFER' => 'CUSTOMER_TO_CUSTOMER_TRANSFER',
            'CUSTOMER_TO_WAREHOUSE_TRANSFER' => 'CUSTOMER_TO_WAREHOUSE_TRANSFER',
            'WAREHOUSE_TO_WAREHOUSE_TRANSFER' => 'WAREHOUSE_TO_WAREHOUSE_TRANSFER',
            'WARRANTY_UPDATE' => 'WARRANTY_UPDATE',
            'MATERIAL_REQUISITION_ISSUED' => 'MATERIAL_REQUISITION_ISSUED',
            'CONTRACT_CREATED' => 'CONTRACT_CREATED',
            'CONTRACT_UPDATED' => 'CONTRACT_UPDATED',
        ];
    }

    #[ArrayShape(['INITIAL_ENTRY' => "string", 'CUSTOMER_TO_CUSTOMER_TRANSFER' => "string",
        'CUSTOMER_TO_WAREHOUSE_TRANSFER' => "string", 'WAREHOUSE_TO_WAREHOUSE_TRANSFER' => "string",
        'WARRANTY_UPDATE' => "string", 'MATERIAL_REQUISITION_ISSUED' => "string",
        'CONTRACT_CREATED'=>"string",'CONTRACT_UPDATED'=>"string"])]
    public static function activityCategoryTitles(): array
    {
        return [
            'INITIAL_ENTRY' => 'Tracking Start',
            'CUSTOMER_TO_CUSTOMER_TRANSFER' => 'Customer/Branch Transfer', //intra + inter
            'CUSTOMER_TO_WAREHOUSE_TRANSFER' => 'Customer to Warehouse Transfer',
            'WAREHOUSE_TO_WAREHOUSE_TRANSFER' => 'Inter-warehouse Transfer',
            'WARRANTY_UPDATE' => 'Warranty updated',
            'MATERIAL_REQUISITION_ISSUED' => 'Store Issue Voucher created',
            'CONTRACT_CREATED' => 'Contract created',
            'CONTRACT_UPDATED' => 'Contract updated',
        ];
    }

}
