<?php

declare(strict_types=1);

namespace App\Exception;

use Symfony\Component\HttpKernel\Attribute\WithHttpStatus;

#[WithHttpStatus(404)]
final class RecordNotFoundException extends \RuntimeException implements BookstoreExceptionInterface
{
    public static function for(string $entityLabel, int $id): self
    {
        return new self(\sprintf('%s de código %d não encontrado.', $entityLabel, $id));
    }
}
