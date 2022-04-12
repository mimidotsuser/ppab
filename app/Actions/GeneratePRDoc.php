<?php

namespace App\Actions;

use App\Models\Company;
use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestActivity;

class GeneratePRDoc
{

    public function __invoke(PurchaseRequest         $purchaseRequest,
                             PurchaseRequestActivity $verification = null,
                             PurchaseRequestActivity $approval = null)
    {

        $company = Company::firstOrFail();
        $engine = new PDFEngine();

        $dompdf = $engine->handle('reports.purchase-request',
            ['pr' => $purchaseRequest, 'company' => $company,
                'verification' => $verification, 'approval' => $approval]
        );

        $dompdf->stream('pr-' . strtolower($purchaseRequest->sn) . ".pdf");
    }

}
