<?php

namespace App\Utils;

use JetBrains\PhpStorm\ArrayShape;

abstract class CustomerUtils
{


    /**
     * @return array
     */
    #[ArrayShape(['LABOUR' => "string", 'FULL' => "string"])]
    public static function getContractTypes(): array
    {
        return [
            'LABOUR' => 'Labour Only',
            'FULL' => 'Full '
        ];
    }
}
