<?php

namespace App\Actions;

use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Facades\View;

class PDFEngine
{
    /**
     * Get the evaluated view contents for the given view.
     *
     * @param string $view
     * @param array $data
     * @return Dompdf
     */
    public function handle(string $view, array $data = [], string $orientation = 'landscape'): Dompdf
    {
        $options = new Options();
        $options->setChroot(public_path(''));
        $options->setIsRemoteEnabled(true);

        $dompdf = new Dompdf();
        $dompdf->setOptions($options);
        $dompdf->setPaper('A4', $orientation);
        $dompdf->loadHtml(View::make($view, $data));
        $dompdf->render();
        return $dompdf;
    }
}
