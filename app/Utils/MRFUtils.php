<?php

namespace App\Utils;

use JetBrains\PhpStorm\ArrayShape;

abstract class MRFUtils
{

    #[ArrayShape(['REPAIR' => "string", 'SALE' => "string", 'STANDBY' => "string",
        'DEMO' => "string", 'LEASE' => "string"])]
    public static function purpose(): array
    {
        return [
            'REPAIR' => 'Machine Repair',
            'SALE' => 'Customer Purchase',
            'STANDBY' => 'Standby',
            'DEMO' => 'Customer Demo',
            'LEASE' => 'Customer Lease',
        ];
    }

    #[ArrayShape(['REQUEST_CREATED' => "string", 'VERIFIED_OKAYED' => "string",
        'VERIFIED_REJECTED' => "string", 'APPROVAL_OKAYED' => "string",
        'APPROVAL_REJECTED' => "string", 'PARTIAL_ISSUE' => "string", 'ISSUED' => "string"])]
    public static function stage(): array
    {
        return [
            'REQUEST_CREATED' => 'REQUEST_CREATED',
            'VERIFIED_OKAYED' => 'VERIFIED_OKAYED',
            'VERIFIED_REJECTED' => 'VERIFIED_REJECTED',
            'APPROVAL_OKAYED' => 'APPROVAL_OKAYED',
            'APPROVAL_REJECTED' => 'APPROVAL_REJECTED',
            'PARTIAL_ISSUE' => 'PARTIAL_ISSUE',
            'ISSUED' => 'ISSUED',
        ];
    }

    #[ArrayShape(['REQUEST_CREATED' => "string", 'VERIFIED_OKAYED' => "string",
        'VERIFIED_REJECTED' => "string", 'APPROVAL_OKAYED' => "string",
        'APPROVAL_REJECTED' => "string", 'PARTIAL_ISSUE' => "string", 'ISSUED' => "string"])]
    public static function outcome()
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
