<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Author;
use App\Exception\RecordInUseException;
use App\Form\AuthorType;
use App\Service\AuthorService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;

#[Route('/authors', name: 'author_')]
final class AuthorController extends AbstractController
{
    public function __construct(private readonly AuthorService $service)
    {
    }

    #[Route('', name: 'index', methods: ['GET'])]
    public function index(#[MapQueryParameter] ?string $search = null, #[MapQueryParameter] int $page = 1): Response
    {
        return $this->render('author/index.html.twig', [
            'page' => $this->service->list($search, $page),
            'search' => $search,
        ]);
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        return $this->handleForm($request, new Author(), 'Autor cadastrado com sucesso.');
    }

    #[Route('/{id}/edit', name: 'edit', requirements: ['id' => Requirement::DIGITS], methods: ['GET', 'POST'])]
    public function edit(Request $request, int $id): Response
    {
        return $this->handleForm($request, $this->service->get($id), 'Autor atualizado com sucesso.');
    }

    #[Route('/{id}/delete', name: 'delete', requirements: ['id' => Requirement::DIGITS], methods: ['POST'])]
    public function delete(Request $request, int $id): Response
    {
        $author = $this->service->get($id);

        if (!$this->isCsrfTokenValid('delete_author_'.$id, $request->getPayload()->getString('_token'))) {
            $this->addFlash('danger', 'Sessão expirada. Recarregue a página e tente novamente.');

            return $this->redirectToRoute('author_index');
        }

        try {
            $this->service->delete($author);
            $this->addFlash('success', 'Autor excluído com sucesso.');
        } catch (RecordInUseException $e) {
            $this->addFlash('danger', $e->getMessage());
        }

        return $this->redirectToRoute('author_index');
    }

    private function handleForm(Request $request, Author $author, string $successMessage): Response
    {
        $form = $this->createForm(AuthorType::class, $author);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->service->save($author);
            $this->addFlash('success', $successMessage);

            return $this->redirectToRoute('author_index');
        }

        return $this->render('author/form.html.twig', ['form' => $form, 'author' => $author]);
    }
}
