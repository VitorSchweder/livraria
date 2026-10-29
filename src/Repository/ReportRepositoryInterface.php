<?php

declare(strict_types=1);

namespace App\Repository;

use App\Report\BookByAuthorRow;

interface ReportRepositoryInterface
{
    /**
     * @return list<BookByAuthorRow>
     */
    public function booksByAuthor(?int $authorId = null): array;
}
