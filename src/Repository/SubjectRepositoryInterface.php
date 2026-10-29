<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Subject;
use App\Pagination\Page;

interface SubjectRepositoryInterface
{
    public function findById(int $id): ?Subject;

    /** @return Page<Subject> */
    public function paginate(?string $search, int $page): Page;

    public function countAll(): int;

    public function save(Subject $subject): void;

    public function delete(Subject $subject): void;
}
