<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Author;
use App\Pagination\Page;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Author>
 */
class AuthorRepository extends ServiceEntityRepository implements AuthorRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Author::class);
    }

    public function findById(int $id): ?Author
    {
        return $this->find($id);
    }

    public function paginate(?string $search, int $page): Page
    {
        $qb = $this->createQueryBuilder('a')->orderBy('a.name', 'ASC');

        $search = trim($search ?? '');
        if (!empty($search)) {
            $qb->andWhere('LOWER(a.name) LIKE LOWER(:search)')
                ->setParameter('search', '%'.$search.'%');
        }

        /** @var Page<Author> $result */
        $result = Page::fromQuery($qb, $page);

        return $result;
    }

    public function findAllOrderedByName(): array
    {
        return $this->findBy([], ['name' => 'ASC']);
    }

    public function countAll(): int
    {
        return $this->count();
    }

    public function save(Author $author): void
    {
        $this->getEntityManager()->persist($author);
        $this->getEntityManager()->flush();
    }

    public function delete(Author $author): void
    {
        $this->getEntityManager()->remove($author);
        $this->getEntityManager()->flush();
    }
}
