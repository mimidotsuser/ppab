<?php

namespace App\Actions;

use App\Models\Company;
use App\Models\GoodsReceiptNote;
use App\Models\GoodsReceiptNoteActivity;

class GenerateRGADoc
{

    public function __invoke(GoodsReceiptNote         $goodsReceiptNote,
                             GoodsReceiptNoteActivity $verification, GoodsReceiptNoteActivity $approval)
    {

        $company = Company::firstOrFail();
        $engine = new PDFEngine();

        $dompdf = $engine->handle('reports.rejected-goods-advice',
            ['receipt' => $goodsReceiptNote, 'company' => $company, 'approval' => $approval,
                'verification' => $verification,
            ]
        );

        return $dompdf;

    }

}
