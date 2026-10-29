<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Entity\Book;
use App\Tests\Support\CreatesRecords;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Field\ChoiceFormField;

final class BookControllerTest extends WebTestCase
{
    use CreatesRecords;

    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    public function testCreatesBookWithPriceFormattedInReais(): void
    {
        $beck = $this->createAuthor('Kent Beck');
        $fowler = $this->createAuthor('Martin Fowler');
        $subject = $this->createSubject('Testes');

        $crawler = $this->client->request('GET', '/books/new');
        $form = $crawler->selectButton('Salvar')->form([
            'book[title]' => 'Refactoring',
            'book[publisher]' => 'Addison-Wesley',
            'book[edition]' => '2',
            'book[publicationYear]' => '2018',
            'book[price]' => '1.234,56',
        ]);
        $authors = $form['book[authors]'];
        $subjects = $form['book[subjects]'];
        self::assertInstanceOf(ChoiceFormField::class, $authors);
        self::assertInstanceOf(ChoiceFormField::class, $subjects);
        $authors->select([(string) $beck->getId(), (string) $fowler->getId()]);
        $subjects->select([(string) $subject->getId()]);
        $this->client->submit($form);

        self::assertResponseRedirects();
        $this->client->followRedirect();
        self::assertSelectorTextContains('.alert-success', 'Livro cadastrado com sucesso.');
        self::assertSelectorTextContains('.badge.text-bg-success', "R$\u{00A0}1.234,56");

        $book = $this->entityManager()->getRepository(Book::class)->findOneBy(['title' => 'Refactoring']);
        self::assertNotNull($book);
        self::assertSame('1234.56', $book->getPrice());
        self::assertCount(2, $book->getAuthors());
    }

    public function testInvalidFormShowsMessagesAndReturns422(): void
    {
        $crawler = $this->client->request('GET', '/books/new');
        $this->client->submit($crawler->selectButton('Salvar')->form([
            'book[title]' => '',
            'book[publisher]' => 'Editora',
            'book[edition]' => '0',
            'book[publicationYear]' => '20',
            'book[price]' => 'abc',
        ]));

        self::assertResponseStatusCodeSame(422);
        $errors = $this->client->getCrawler()->filter('.invalid-feedback')->each(static fn ($node) => trim($node->text()));
        self::assertContains('Informe o título.', $errors);
        self::assertContains('A edição deve ser maior que zero.', $errors);
        self::assertContains('O ano de publicação deve ter 4 dígitos (ex.: 2024).', $errors);
        self::assertContains('Informe um valor válido (ex.: 59,90).', $errors);
        self::assertContains('Selecione ao menos um autor.', $errors);
    }

    public function testListShowsPriceInReaisAndAuthors(): void
    {
        $this->createBook('Clean Code', [$this->createAuthor('Robert C. Martin')], [$this->createSubject('Eng. de Software')], '189.90');

        $this->client->request('GET', '/books?search=clean');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('tbody', 'Clean Code');
        self::assertSelectorTextContains('tbody', 'Robert C. Martin');
        self::assertSelectorTextContains('tbody', "R$\u{00A0}189,90");
    }

    public function testSearchByAuthorKeepsAllAuthorsOfTheBook(): void
    {
        $this->createBook('Refactoring', [$this->createAuthor('Martin Fowler'), $this->createAuthor('Kent Beck')], [$this->createSubject('Testes')]);
        $this->entityManager()->clear();

        $this->client->request('GET', '/books?search=fowler');

        self::assertSelectorTextContains('tbody', 'Kent Beck, Martin Fowler');
    }

    public function testEditsBook(): void
    {
        $book = $this->createBook('Old Title', [$this->createAuthor('Author')], [$this->createSubject('Subject')]);

        $crawler = $this->client->request('GET', \sprintf('/books/%d/edit', $book->getId()));
        self::assertInputValueSame('book[price]', '50,00');

        $this->client->submit($crawler->selectButton('Salvar')->form(['book[title]' => 'New Title']));

        self::assertResponseRedirects(\sprintf('/books/%d', $book->getId()));
        $this->client->followRedirect();
        self::assertSelectorTextContains('h1', 'New Title');
    }

    public function testDeletesBook(): void
    {
        $book = $this->createBook('To Delete', [$this->createAuthor('Author')], [$this->createSubject('Subject')]);
        $bookId = $book->getId();

        $crawler = $this->client->request('GET', '/books');
        $this->client->submit($crawler->filter(\sprintf('form[action="/books/%d/delete"]', $bookId))->form());

        self::assertResponseRedirects('/books');
        $this->client->followRedirect();
        self::assertSelectorTextContains('.alert-success', 'Livro excluído com sucesso.');
        $this->entityManager()->clear();
        self::assertNull($this->entityManager()->find(Book::class, $bookId));
    }

    public function testDeleteWithInvalidTokenKeepsBook(): void
    {
        $book = $this->createBook('Protected', [$this->createAuthor('Author')], [$this->createSubject('Subject')]);

        $this->client->request('POST', \sprintf('/books/%d/delete', $book->getId()), ['_token' => 'invalid']);

        self::assertResponseRedirects('/books');
        $this->client->followRedirect();
        self::assertSelectorTextContains('.alert-danger', 'Sessão expirada');
        $this->entityManager()->clear();
        self::assertNotNull($this->entityManager()->find(Book::class, $book->getId()));
    }

    public function testMissingBookReturns404(): void
    {
        $this->client->request('GET', '/books/999999');

        self::assertResponseStatusCodeSame(404);
    }
}
