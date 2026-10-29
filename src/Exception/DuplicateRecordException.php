<?php

declare(strict_types=1);

namespace App\Exception;

use Symfony\Component\HttpKernel\Attribute\WithHttpStatus;

#[WithHttpStatus(409)]
final class DuplicateRecordException extends \RuntimeException implements BookstoreExceptionInterface
{
    public static function for(string $entityLabel, string $description, ?\Throwable $previous = null): self
    {
        return new self(\sprintf('Já existe um %s "%s" cadastrado.', $entityLabel, $description), previous: $previous);
    }
}
