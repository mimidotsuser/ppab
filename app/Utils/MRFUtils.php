<?php

namespace App\Utils;

use JetBrains\PhpStorm\ArrayShape;

abstract class MRFUtils
{

    #[ArrayShape(['repair' => "string", 'sale' => "string", 'standby' => "string",
        'demo' => "string", 'lease' => "string"])]
    public static function purpose(): array
    {
        return [
            'repair' => 'Machine Repair',
            'sale' => 'Customer Purchase',
            'standby' => 'Standby',
            'demo' => 'Customer Demo',
            'lease' => 'Customer Lease',
        ];
    }

    public static function stage(): array
    {
        return [
            'REQUEST_CREATED' => 'Request Created',
            'VERIFIED_OKAYED' => 'Verified',
            'VERIFIED_REJECTED' => 'All Rejected',
            'APPROVAL_OKAYED' => 'Approved',
            'APPROVAL_REJECTED' => 'All Rejected',
            'PARTIAL_ISSUE' => 'Partially Issued',
            'ISSUED' => 'Issue complete',
        ];
    }


}
