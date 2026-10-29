<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Tests\Support\CreatesRecords;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class HomeControllerTest extends WebTestCase
{
    use CreatesRecords;

    public function testShowsMenuAndTotals(): void
    {
        $client = static::createClient();
        $this->createBook('Clean Code', [$this->createAuthor('Robert C. Martin'), $this->createAuthor('Other')], [$this->createSubject('Eng. de Software')]);

        $client->request('GET', '/');

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('nav a[href="/books"]');
        self::assertSelectorExists('nav a[href="/authors"]');
        self::assertSelectorExists('nav a[href="/subjects"]');
        self::assertSelectorExists('nav a[href="/reports/books-by-author"]');
        self::assertSelectorTextSame('[data-total="books"]', '1');
        self::assertSelectorTextSame('[data-total="authors"]', '2');
        self::assertSelectorTextSame('[data-total="subjects"]', '1');
    }
}
