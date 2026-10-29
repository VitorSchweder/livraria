<?php

declare(strict_types=1);

namespace App\Exception;

use Symfony\Component\HttpKernel\Attribute\WithHttpStatus;

#[WithHttpStatus(409)]
final class RecordInUseException extends \RuntimeException implements BookstoreExceptionInterface
{
    public static function linkedToBooks(string $entityLabel, string $description, ?\Throwable $previous = null): self
    {
        return new self(
            \sprintf('Não é possível excluir o %s "%s": ele está vinculado a um ou mais livros.', $entityLabel, $description),
            previous: $previous,
        );
    }
}
