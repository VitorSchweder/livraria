<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Book;
use App\Pagination\Page;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Book>
 */
class BookRepository extends ServiceEntityRepository implements BookRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Book::class);
    }

    public function findById(int $id): ?Book
    {
        return $this->createQueryBuilder('b')
            ->leftJoin('b.authors', 'a')->addSelect('a')
            ->leftJoin('b.subjects', 's')->addSelect('s')
            ->where('b.id = :id')
            ->orderBy('a.name', 'ASC')
            ->addOrderBy('s.description', 'ASC')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function paginate(?string $search, int $page): Page
    {
        $qb = $this->createQueryBuilder('b')
            ->leftJoin('b.authors', 'a')->addSelect('a')
            ->leftJoin('b.subjects', 's')->addSelect('s')
            ->orderBy('b.title', 'ASC')
            ->addOrderBy('b.id', 'ASC')
            ->addOrderBy('a.name', 'ASC')
            ->addOrderBy('s.description', 'ASC');

        $search = trim($search ?? '');
        if (!empty($search)) {
            $booksByAuthor = $this->getEntityManager()->createQueryBuilder()
                ->select('ba.id')
                ->from(Book::class, 'ba')
                ->join('ba.authors', 'aa')
                ->where('LOWER(aa.name) LIKE LOWER(:search)')
                ->getDQL();

            $qb->andWhere(\sprintf('LOWER(b.title) LIKE LOWER(:search) OR LOWER(b.publisher) LIKE LOWER(:search) OR b.id IN (%s)', $booksByAuthor))
                ->setParameter('search', '%'.$search.'%');
        }

        /** @var Page<Book> $result */
        $result = Page::fromQuery($qb, $page, fetchJoinCollection: true);

        return $result;
    }

    public function countAll(): int
    {
        return $this->count();
    }

    public function save(Book $book): void
    {
        $this->getEntityManager()->persist($book);
        $this->getEntityManager()->flush();
    }

    public function delete(Book $book): void
    {
        $this->getEntityManager()->remove($book);
        $this->getEntityManager()->flush();
    }
}
