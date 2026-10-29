<?php

declare(strict_types=1);

namespace App\Tests\Unit\Pagination;

use App\Pagination\Page;
use PHPUnit\Framework\TestCase;

final class PageTest extends TestCase
{
    public function testComputesTotalPages(): void
    {
        self::assertSame(3, new Page([], 1, 10, 21)->totalPages());
        self::assertSame(1, new Page([], 1, 10, 0)->totalPages());
    }

    public function testNavigation(): void
    {
        $first = new Page([], 1, 10, 25);
        $last = new Page([], 3, 10, 25);

        self::assertFalse($first->hasPreviousPage());
        self::assertTrue($first->hasNextPage());
        self::assertTrue($last->hasPreviousPage());
        self::assertFalse($last->hasNextPage());
    }
}
