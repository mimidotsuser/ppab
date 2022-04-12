<?php

namespace App\Actions;

use App\Models\Company;
use App\Models\GoodsReceiptNote;
use App\Models\GoodsReceiptNoteActivity;

class GenerateGRNDoc
{

    public function __invoke(GoodsReceiptNote         $goodsReceiptNote,
                             GoodsReceiptNoteActivity $verification, GoodsReceiptNoteActivity $approval)
    {

        $company = Company::firstOrFail();
        $engine = new PDFEngine();

        $dompdf = $engine->handle('reports.goods-receipt-note',
            ['receipt' => $goodsReceiptNote, 'company' => $company, 'approval' => $approval,
                'verification' => $verification,
            ]
        );

        return $dompdf;
    }

}
