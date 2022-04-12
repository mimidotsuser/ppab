<?php

namespace App\Actions;

use App\Models\Company;
use App\Models\PurchaseOrder;
use Dompdf\Dompdf;

class GeneratePODoc
{

    public function __invoke(PurchaseOrder $purchaseOrder, $total = 0,): Dompdf
    {

        $company = Company::firstOrFail();
        $engine = new PDFEngine();

        $dompdf = $engine->handle('reports.purchase-order',
            ['purchaseOrder' => $purchaseOrder, 'company' => $company, 'total' => $total,
                'vendor' => $purchaseOrder->vendor]
        );

        return $dompdf;

    }

}
