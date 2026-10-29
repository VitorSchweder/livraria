<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Entity\Author;
use App\Tests\Support\CreatesRecords;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class AuthorControllerTest extends WebTestCase
{
    use CreatesRecords;

    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    public function testCreatesAuthor(): void
    {
        $crawler = $this->client->request('GET', '/authors/new');
        $this->client->submit($crawler->selectButton('Salvar')->form(['author[name]' => '  Clarice Lispector ']));

        self::assertResponseRedirects('/authors');
        $this->client->followRedirect();
        self::assertSelectorTextContains('.alert-success', 'Autor cadastrado com sucesso.');
        self::assertSelectorTextContains('tbody', 'Clarice Lispector');
        self::assertNotNull($this->entityManager()->getRepository(Author::class)->findOneBy(['name' => 'Clarice Lispector']));
    }

    public function testRejectsNameAboveModelLimit(): void
    {
        $crawler = $this->client->request('GET', '/authors/new');
        $this->client->submit($crawler->selectButton('Salvar')->form(['author[name]' => str_repeat('a', 41)]));

        self::assertResponseStatusCodeSame(422);
        self::assertSelectorTextContains('.invalid-feedback', 'no máximo 40 caracteres');
    }

    public function testEditsAuthor(): void
    {
        $author = $this->createAuthor('Old Name');

        $crawler = $this->client->request('GET', \sprintf('/authors/%d/edit', $author->getId()));
        $this->client->submit($crawler->selectButton('Salvar')->form(['author[name]' => 'New Name']));

        self::assertResponseRedirects('/authors');
        $this->client->followRedirect();
        self::assertSelectorTextContains('.alert-success', 'Autor atualizado com sucesso.');
        self::assertSelectorTextContains('tbody', 'New Name');
    }

    public function testDeletingLinkedAuthorShowsMessageAndKeepsRecord(): void
    {
        $author = $this->createAuthor('Kent Beck');
        $this->createBook('TDD', [$author], [$this->createSubject('Testes')]);

        $crawler = $this->client->request('GET', '/authors');
        $this->client->submit($crawler->filter(\sprintf('form[action="/authors/%d/delete"]', $author->getId()))->form());

        self::assertResponseRedirects('/authors');
        $this->client->followRedirect();
        self::assertSelectorTextContains('.alert-danger', 'Não é possível excluir o autor "Kent Beck": ele está vinculado a um ou mais livros.');
        self::assertSelectorTextContains('tbody', 'Kent Beck');
    }

    public function testDeletesAuthorWithoutBooks(): void
    {
        $author = $this->createAuthor('Free Author');

        $crawler = $this->client->request('GET', '/authors');
        $this->client->submit($crawler->filter(\sprintf('form[action="/authors/%d/delete"]', $author->getId()))->form());

        self::assertResponseRedirects('/authors');
        $this->client->followRedirect();
        self::assertSelectorTextContains('.alert-success', 'Autor excluído com sucesso.');
    }

    public function testMissingAuthorReturns404(): void
    {
        $this->client->request('GET', '/authors/999999/edit');

        self::assertResponseStatusCodeSame(404);
    }
}
