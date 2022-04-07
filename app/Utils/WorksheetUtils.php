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
    public static function worksheetCategoryCodes(): array
    {
        return [
            'REPAIR' => 'REPAIR',
            'GENERAL_SERVICING' => 'GENERAL_SERVICING',
            'TRAINING_AND_INSTALLATION' => 'TRAINING_AND_INSTALLATION',
            'DELIVERY_AND_INSTALLATION' => 'DELIVERY_AND_INSTALLATION',
            'TECHNICAL_REPORT' => 'TECHNICAL_REPORT',
            'OTHER' => 'OTHER'
        ];
    }

    #[ArrayShape(['REPAIR' => "string", 'GENERAL_SERVICING' => "string",
        'TRAINING_AND_INSTALLATION' => "string", 'DELIVERY_AND_INSTALLATION' => "string",
        'TECHNICAL_REPORT' => "string", 'OTHER' => "string"])]
    public static function worksheetCategoryTitles(): array
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
