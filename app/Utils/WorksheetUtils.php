<?php

namespace App\Utils;

use JetBrains\PhpStorm\ArrayShape;

abstract class WorksheetUtils
{


    /**
     * @return array
     */
    #[ArrayShape(['REPAIR' => "string", 'GENERAL_SERVICING' => "string",
        'TRAINING_AND_INSTALLATION' => "string", 'DELIVERY_AND_INSTALLATION' => "string",
        'TECHNICAL_REPORT' => "string", 'OTHER' => "string"])]
    public static function getWorksheetCategories(): array
    {
        return [
            'REPAIR' => 'Machine Repair',
            'GENERAL_SERVICING' => 'Service and Maintenance',
            'TRAINING_AND_INSTALLATION' => 'Training and Installation',
            'DELIVERY_AND_INSTALLATION' => 'Delivery and Installation',
            'TECHNICAL_REPORT' => 'Technical Report',
            'OTHER' => 'Other'
        ];
    }
}
