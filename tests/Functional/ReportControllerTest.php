<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Tests\Support\CreatesRecords;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ReportControllerTest extends WebTestCase
{
    use CreatesRecords;

    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    public function testGroupsByAuthorWithMultiAuthorBookInEachGroup(): void
    {
        $beck = $this->createAuthor('Kent Beck');
        $fowler = $this->createAuthor('Martin Fowler');
        $this->createBook('Refactoring', [$beck, $fowler], [$this->createSubject('Testes')], '249.90');

        $crawler = $this->client->request('GET', '/reports/books-by-author');

        self::assertResponseIsSuccessful();
        $groups = $crawler->filter('.author-group')->each(static fn ($group) => [
            'author' => trim($group->filter('h2')->text()),
            'books' => $group->filter('tbody tr td:first-child')->each(static fn ($td) => $td->text()),
        ]);
        $booksByAuthor = array_column($groups, 'books', 'author');

        self::assertSame(['Refactoring'], $booksByAuthor['Kent Beck']);
        self::assertSame(['Refactoring'], $booksByAuthor['Martin Fowler']);
        self::assertSelectorTextContains('.author-group', "R$\u{00A0}249,90");
    }

    public function testFiltersByAuthor(): void
    {
        $beck = $this->createAuthor('Kent Beck');
        $this->createAuthor('Other Author');

        $crawler = $this->client->request('GET', '/reports/books-by-author?author='.$beck->getId());

        self::assertCount(1, $crawler->filter('.author-group'));
        self::assertSelectorTextContains('.author-group h2', 'Kent Beck');
    }

    public function testGeneratesPdf(): void
    {
        $this->createBook('Clean Code', [$this->createAuthor('Robert C. Martin')], [$this->createSubject('Eng. de Software')]);

        $this->client->request('GET', '/reports/books-by-author/pdf');

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('Content-Type', 'application/pdf');
        self::assertStringStartsWith('inline; filename=livros-por-autor-', (string) $this->client->getResponse()->headers->get('Content-Disposition'));
        self::assertStringStartsWith('%PDF-', (string) $this->client->getResponse()->getContent());
    }
}
