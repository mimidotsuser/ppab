<?php

namespace App\Actions;

use App\Models\Company;
use App\Models\MaterialRequisition;
use App\Models\MaterialRequisitionActivity;
use Dompdf\Dompdf;

class GenerateSIVDoc
{

    public function __invoke(MaterialRequisition         $materialRequisition,
                             MaterialRequisitionActivity $approval, MaterialRequisitionActivity $issue): Dompdf
    {

        $company = Company::firstOrFail();
        $engine = new PDFEngine();

        $dompdf = $engine->handle('reports.store-issue-voucher',
            ['mrn' => $materialRequisition, 'company' => $company, 'issue' => $issue, 'approval' => $approval]
        );

        return $dompdf;

    }

}
