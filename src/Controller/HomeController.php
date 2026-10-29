<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\AuthorService;
use App\Service\BookService;
use App\Service\SubjectService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'home', methods: ['GET'])]
    public function __invoke(BookService $books, AuthorService $authors, SubjectService $subjects): Response
    {
        return $this->render('home/index.html.twig', [
            'bookCount' => $books->count(),
            'authorCount' => $authors->count(),
            'subjectCount' => $subjects->count(),
        ]);
    }
}
