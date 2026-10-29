<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Subject;
use App\Exception\DuplicateRecordException;
use App\Exception\RecordInUseException;
use App\Form\SubjectType;
use App\Service\SubjectService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;

#[Route('/subjects', name: 'subject_')]
final class SubjectController extends AbstractController
{
    public function __construct(private readonly SubjectService $service)
    {
    }

    #[Route('', name: 'index', methods: ['GET'])]
    public function index(#[MapQueryParameter] ?string $search = null, #[MapQueryParameter] int $page = 1): Response
    {
        return $this->render('subject/index.html.twig', [
            'page' => $this->service->list($search, $page),
            'search' => $search,
        ]);
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        return $this->handleForm($request, new Subject(), 'Assunto cadastrado com sucesso.');
    }

    #[Route('/{id}/edit', name: 'edit', requirements: ['id' => Requirement::DIGITS], methods: ['GET', 'POST'])]
    public function edit(Request $request, int $id): Response
    {
        return $this->handleForm($request, $this->service->get($id), 'Assunto atualizado com sucesso.');
    }

    #[Route('/{id}/delete', name: 'delete', requirements: ['id' => Requirement::DIGITS], methods: ['POST'])]
    public function delete(Request $request, int $id): Response
    {
        $subject = $this->service->get($id);

        if (!$this->isCsrfTokenValid('delete_subject_'.$id, $request->getPayload()->getString('_token'))) {
            $this->addFlash('danger', 'Sessão expirada. Recarregue a página e tente novamente.');

            return $this->redirectToRoute('subject_index');
        }

        try {
            $this->service->delete($subject);
            $this->addFlash('success', 'Assunto excluído com sucesso.');
        } catch (RecordInUseException $e) {
            $this->addFlash('danger', $e->getMessage());
        }

        return $this->redirectToRoute('subject_index');
    }

    private function handleForm(Request $request, Subject $subject, string $successMessage): Response
    {
        $form = $this->createForm(SubjectType::class, $subject);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->service->save($subject);
                $this->addFlash('success', $successMessage);

                return $this->redirectToRoute('subject_index');
            } catch (DuplicateRecordException $e) {
                $form->get('description')->addError(new FormError($e->getMessage()));
            }
        }

        return $this->render('subject/form.html.twig', ['form' => $form, 'subject' => $subject]);
    }
}
