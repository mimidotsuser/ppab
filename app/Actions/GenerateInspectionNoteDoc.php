<?php

namespace App\Actions;

use App\Models\Company;
use App\Models\GoodsReceiptNoteActivity;
use App\Models\InspectionNote;
use Dompdf\Dompdf;

class GenerateInspectionNoteDoc
{

    public function __invoke(InspectionNote $note, GoodsReceiptNoteActivity $inspection): Dompdf
    {

        $company = Company::firstOrFail();
        $engine = new PDFEngine();

        $dompdf = $engine->handle('reports.inspection-note',
            ['note' => $note, 'company' => $company, 'inspection' => $inspection]
        );

        return $dompdf;
    }

}
