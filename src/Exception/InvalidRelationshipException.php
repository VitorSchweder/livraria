<?php

declare(strict_types=1);

namespace App\Exception;

use Symfony\Component\HttpKernel\Attribute\WithHttpStatus;

#[WithHttpStatus(422)]
final class InvalidRelationshipException extends \RuntimeException implements BookstoreExceptionInterface
{
    public static function authorOrSubjectRemoved(?\Throwable $previous = null): self
    {
        return new self(
            'Um dos autores ou assuntos selecionados não existe mais. Atualize a página e tente novamente.',
            previous: $previous,
        );
    }
}
