<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Subject;
use App\Pagination\Page;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Subject>
 */
class SubjectRepository extends ServiceEntityRepository implements SubjectRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Subject::class);
    }

    public function findById(int $id): ?Subject
    {
        return $this->find($id);
    }

    public function paginate(?string $search, int $page): Page
    {
        $qb = $this->createQueryBuilder('s')->orderBy('s.description', 'ASC');

        $search = trim($search ?? '');
        if (!empty($search)) {
            $qb->andWhere('LOWER(s.description) LIKE LOWER(:search)')
                ->setParameter('search', '%'.$search.'%');
        }

        /** @var Page<Subject> $result */
        $result = Page::fromQuery($qb, $page);

        return $result;
    }

    public function countAll(): int
    {
        return $this->count();
    }

    public function save(Subject $subject): void
    {
        $this->getEntityManager()->persist($subject);
        $this->getEntityManager()->flush();
    }

    public function delete(Subject $subject): void
    {
        $this->getEntityManager()->remove($subject);
        $this->getEntityManager()->flush();
    }
}
