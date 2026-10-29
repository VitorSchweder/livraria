<?php

declare(strict_types=1);

namespace App\Report\Pdf;

interface PdfRendererInterface
{
    public function render(string $html, string $orientation = 'portrait'): string;
}
