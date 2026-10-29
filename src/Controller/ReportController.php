<?php

declare(strict_types=1);

namespace App\Controller;

use App\Report\Pdf\PdfRendererInterface;
use App\Service\AuthorService;
use App\Service\ReportService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/reports/books-by-author', name: 'report_books_by_author')]
final class ReportController extends AbstractController
{
    public function __construct(
        private readonly ReportService $reports,
        private readonly AuthorService $authors,
    ) {
    }

    #[Route('', name: '', methods: ['GET'])]
    public function show(#[MapQueryParameter] ?int $author = null): Response
    {
        return $this->render('report/books_by_author.html.twig', [
            'report' => $this->reports->booksByAuthor($author),
            'authors' => $this->authors->all(),
            'selectedAuthor' => $author,
        ]);
    }

    #[Route('/pdf', name: '_pdf', methods: ['GET'])]
    public function pdf(PdfRendererInterface $pdf, #[MapQueryParameter] ?int $author = null): Response
    {
        $report = $this->reports->booksByAuthor($author);
        $html = $this->renderView('report/books_by_author.pdf.twig', ['report' => $report]);

        $filename = \sprintf('livros-por-autor-%s.pdf', $report->generatedAt->format('Y-m-d-His'));

        return new Response($pdf->render($html), Response::HTTP_OK, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => HeaderUtils::makeDisposition(HeaderUtils::DISPOSITION_INLINE, $filename),
        ]);
    }
}
