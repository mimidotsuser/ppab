<?php

namespace App\Actions;

use App\Models\Company;
use App\Models\RequestForQuotation;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use ZipArchive;

class GenerateRFQDocs
{

    public function __invoke(RequestForQuotation $requestForQuotation): string
    {


        $company = Company::firstOrFail();
        $engine = new PDFEngine();
        $zip = new ZipArchive();

        Storage::disk('local')->makeDirectory('temp/');

        $zipFilePath = Storage::disk('local')->path('temp/') . Str::random(10) . '.zip';

        $zip->open($zipFilePath, \ZipArchive::CREATE | ZipArchive::OVERWRITE);

        foreach ($requestForQuotation->vendors as $vendor) {

            $dompdf = $engine->handle('reports.request-for-quotation',
                ['rfq' => $requestForQuotation, 'company' => $company, 'vendor' => $vendor]
            );

            $filename = $vendor->name . ' rfq-' . strtolower($requestForQuotation->sn) . ".pdf";

            $zip->addFromString($filename, $dompdf->output());
        }

        $zip->close();

        return  $zipFilePath;
    }

}
