<?php

namespace App\Actions;

use App\Models\Company;
use App\Models\MaterialRequisition;
use App\Models\MaterialRequisitionActivity;
use Dompdf\Dompdf;

class GenerateMRNDoc
{

    public function __invoke(MaterialRequisition         $materialRequisition,
                             MaterialRequisitionActivity $verification,
                             MaterialRequisitionActivity $approval): Dompdf
    {

        $company = Company::firstOrFail();
        $engine = new PDFEngine();

        $dompdf = $engine->handle('reports.material-requisition-note',
            ['mrn' => $materialRequisition, 'company' => $company, 'verification' => $verification,
                'approval' => $approval]
        );

        return $dompdf;

    }

}
