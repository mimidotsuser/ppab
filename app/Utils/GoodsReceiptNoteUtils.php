<?php

namespace App\Utils;

use JetBrains\PhpStorm\ArrayShape;

abstract class GoodsReceiptNoteUtils
{

    #[ArrayShape(['REQUEST_CREATED' => "string", 'INSPECTION_DONE' => "string",
        'APPROVAL_OKAYED' => "string", 'APPROVAL_REJECTED' => "string"])]
    public static function stage(): array
    {
        return [
            'REQUEST_CREATED' => 'REQUEST_CREATED',
            'INSPECTION_DONE' => 'INSPECTION_DONE',
            'APPROVAL_OKAYED' => 'APPROVAL_OKAYED',
            'APPROVAL_REJECTED' => 'APPROVAL_REJECTED',
        ];
    }


    #[ArrayShape(['REQUEST_CREATED' => "string", 'INSPECTION_DONE' => "string",
        'APPROVAL_OKAYED' => "string", 'APPROVAL_REJECTED' => "string"])]
    public static function outcome()
    {
        return [
            'REQUEST_CREATED' => 'Request Created',
            'INSPECTION_DONE' => 'Inspection Done',
            'APPROVAL_OKAYED' => 'Approved',
            'APPROVAL_REJECTED' => 'All Rejected',
        ];
    }
}
