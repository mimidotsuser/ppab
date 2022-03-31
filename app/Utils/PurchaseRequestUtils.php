<?php

namespace App\Utils;

use JetBrains\PhpStorm\ArrayShape;

abstract class PurchaseRequestUtils
{

    #[ArrayShape(['REQUEST_CREATED' => "string", 'VERIFIED_OKAYED' => "string",
        'VERIFIED_REJECTED' => "string", 'APPROVAL_OKAYED' => "string",
        'APPROVAL_REJECTED' => "string"])]
    public static function stage(): array
    {
        return [
            'REQUEST_CREATED' => 'REQUEST_CREATED',
            'VERIFIED_OKAYED' => 'VERIFIED_OKAYED',
            'VERIFIED_REJECTED' => 'VERIFIED_REJECTED',
            'APPROVAL_OKAYED' => 'APPROVAL_OKAYED',
            'APPROVAL_REJECTED' => 'APPROVAL_REJECTED'
        ];
    }

    #[ArrayShape(['REQUEST_CREATED' => "string", 'VERIFIED_OKAYED' => "string",
        'VERIFIED_REJECTED' => "string", 'APPROVAL_OKAYED' => "string",
        'APPROVAL_REJECTED' => "string",])]
    public static function outcome()
    {
        return [
            'REQUEST_CREATED' => 'Request Created',
            'VERIFIED_OKAYED' => 'Verified',
            'VERIFIED_REJECTED' => 'All Rejected',
            'APPROVAL_OKAYED' => 'Approved',
            'APPROVAL_REJECTED' => 'All Rejected',
        ];
    }
}
