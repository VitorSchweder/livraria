<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Book;
use App\Exception\InvalidRelationshipException;
use App\Form\BookType;
use App\Service\BookService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;

#[Route('/books', name: 'book_')]
final class BookController extends AbstractController
{
    public function __construct(private readonly BookService $service)
    {
    }

    #[Route('', name: 'index', methods: ['GET'])]
    public function index(#[MapQueryParameter] ?string $search = null, #[MapQueryParameter] int $page = 1): Response
    {
        return $this->render('book/index.html.twig', [
            'page' => $this->service->list($search, $page),
            'search' => $search,
        ]);
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        return $this->handleForm($request, new Book(), 'Livro cadastrado com sucesso.');
    }

    #[Route('/{id}', name: 'show', requirements: ['id' => Requirement::DIGITS], methods: ['GET'])]
    public function show(int $id): Response
    {
        return $this->render('book/show.html.twig', ['book' => $this->service->get($id)]);
    }

    #[Route('/{id}/edit', name: 'edit', requirements: ['id' => Requirement::DIGITS], methods: ['GET', 'POST'])]
    public function edit(Request $request, int $id): Response
    {
        return $this->handleForm($request, $this->service->get($id), 'Livro atualizado com sucesso.');
    }

    #[Route('/{id}/delete', name: 'delete', requirements: ['id' => Requirement::DIGITS], methods: ['POST'])]
    public function delete(Request $request, int $id): Response
    {
        $book = $this->service->get($id);

        if (!$this->isCsrfTokenValid('delete_book_'.$id, $request->getPayload()->getString('_token'))) {
            $this->addFlash('danger', 'Sessão expirada. Recarregue a página e tente novamente.');

            return $this->redirectToRoute('book_index');
        }

        $this->service->delete($book);
        $this->addFlash('success', 'Livro excluído com sucesso.');

        return $this->redirectToRoute('book_index');
    }

    private function handleForm(Request $request, Book $book, string $successMessage): Response
    {
        $form = $this->createForm(BookType::class, $book);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->service->save($book);
                $this->addFlash('success', $successMessage);

                return $this->redirectToRoute('book_show', ['id' => $book->getId()]);
            } catch (InvalidRelationshipException $e) {
                $form->addError(new FormError($e->getMessage()));
            }
        }

        return $this->render('book/form.html.twig', ['form' => $form, 'book' => $book]);
    }
}
