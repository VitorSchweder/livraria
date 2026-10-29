<?php

declare(strict_types=1);

namespace App\Repository;

use App\Report\BookByAuthorRow;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;

final readonly class ReportRepository implements ReportRepositoryInterface
{
    public const string VIEW = 'vw_livros_por_autor';

    public function __construct(private Connection $connection)
    {
    }

    public function booksByAuthor(?int $authorId = null): array
    {
        $qb = $this->connection->createQueryBuilder()
            ->select('*')
            ->from(self::VIEW)
            ->orderBy('autor_nome')
            ->addOrderBy('autor_codau')
            ->addOrderBy('titulo')
            ->addOrderBy('livro_codl');

        if (!empty($authorId)) {
            $qb->where('autor_codau = :authorId')->setParameter('authorId', $authorId, ParameterType::INTEGER);
        }

        return array_map(
            BookByAuthorRow::fromView(...),
            $qb->executeQuery()->fetchAllAssociative(),
        );
    }
}
