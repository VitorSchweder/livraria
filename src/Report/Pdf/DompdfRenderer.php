<?php

declare(strict_types=1);

namespace App\Report\Pdf;

use Dompdf\Dompdf;
use Dompdf\Options;

final readonly class DompdfRenderer implements PdfRendererInterface
{
    public function render(string $html, string $orientation = 'portrait'): string
    {
        $options = new Options();
        $options->setDefaultFont('DejaVu Sans');
        $options->setIsRemoteEnabled(false);
        $options->setIsPhpEnabled(false);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', $orientation);
        $dompdf->render();

        return (string) $dompdf->output();
    }
}
