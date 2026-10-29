<?php

declare(strict_types=1);

namespace App\Report;

final readonly class BookByAuthorRow
{
    /**
     * @param list<string> $subjects
     * @param list<string> $coAuthors
     */
    public function __construct(
        public int $authorId,
        public string $authorName,
        public ?int $bookId,
        public ?string $title,
        public ?string $publisher,
        public ?int $edition,
        public ?string $publicationYear,
        public ?string $price,
        public array $subjects,
        public array $coAuthors,
    ) {
    }

    /**
     * @param array<string, mixed> $row
     */
    public static function fromView(array $row): self
    {
        return new self(
            authorId: (int) $row['autor_codau'],
            authorName: (string) $row['autor_nome'],
            bookId: empty($row['livro_codl']) ? null : (int) $row['livro_codl'],
            title: $row['titulo'],
            publisher: $row['editora'],
            edition: empty($row['edicao']) ? null : (int) $row['edicao'],
            publicationYear: $row['anopublicacao'],
            price: $row['valor'] === null ? null : (string) $row['valor'],
            subjects: self::split($row['assuntos']),
            coAuthors: self::split($row['coautores']),
        );
    }

    public function hasBook(): bool
    {
        return !empty($this->bookId);
    }

    public function priceInCents(): int
    {
        return empty($this->price) ? 0 : (int) round(((float) $this->price) * 100);
    }

    /**
     * @return list<string>
     */
    private static function split(mixed $value): array
    {
        if (empty($value)) {
            return [];
        }

        return array_values(array_filter(explode('|', (string) $value), static fn (string $item): bool => $item !== ''));
    }
}
