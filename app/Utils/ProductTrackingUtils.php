<?php

namespace App\Utils;

use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\Pure;

abstract class ProductTrackingUtils
{

    /**
     * @return string[]
     */
    #[Pure] #[ArrayShape(['REPAIR' => "string",
        'GENERAL_SERVICING' => "string", 'TRAINING_AND_INSTALLATION' => "string",
        'DELIVERY_AND_INSTALLATION' => "string", 'TECHNICAL_REPORT' => "string",
        'OTHER' => "string"])]
    public static function getLogCategories(): array
    {
        return array_merge(WorksheetUtils::getWorksheetCategories(), [
            'CONTRACT_ASSIGNED' => 'Contract Created',
            'CONTRACT_UPDATED' => 'Contract Updated',
            'INITIAL_ENTRY' => 'Tracking Start',
            'DEMO_CHECKIN' => 'Out of Demo',
            'STANDBY_CHECKIN' => 'Standby Reminder',
            'LEASE_CHECKIN' => 'Out of Lease',
            'CUSTOMER_TRANSFER' => 'Customer/Branch Transfer',
        ]);
    }
}
