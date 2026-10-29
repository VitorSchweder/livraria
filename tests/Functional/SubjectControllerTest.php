<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Tests\Support\CreatesRecords;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class SubjectControllerTest extends WebTestCase
{
    use CreatesRecords;

    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    public function testCreatesSubject(): void
    {
        $crawler = $this->client->request('GET', '/subjects/new');
        $this->client->submit($crawler->selectButton('Salvar')->form(['subject[description]' => 'Fantasia']));

        self::assertResponseRedirects('/subjects');
        $this->client->followRedirect();
        self::assertSelectorTextContains('.alert-success', 'Assunto cadastrado com sucesso.');
        self::assertSelectorTextContains('tbody', 'Fantasia');
    }

    public function testRejectsDuplicateDescription(): void
    {
        $this->createSubject('Romance');

        $crawler = $this->client->request('GET', '/subjects/new');
        $this->client->submit($crawler->selectButton('Salvar')->form(['subject[description]' => 'Romance']));

        self::assertResponseStatusCodeSame(422);
        self::assertSelectorTextContains('.invalid-feedback', 'Já existe um assunto com esta descrição.');
    }

    public function testRejectsDescriptionLongerThan20Chars(): void
    {
        $crawler = $this->client->request('GET', '/subjects/new');
        $this->client->submit($crawler->selectButton('Salvar')->form(['subject[description]' => 'Engenharia de Software']));

        self::assertResponseStatusCodeSame(422);
        self::assertSelectorTextContains('.invalid-feedback', 'no máximo 20 caracteres');
    }

    public function testDeletingLinkedSubjectShowsMessage(): void
    {
        $subject = $this->createSubject('Testes');
        $this->createBook('TDD', [$this->createAuthor('Kent Beck')], [$subject]);

        $crawler = $this->client->request('GET', '/subjects');
        $this->client->submit($crawler->filter(\sprintf('form[action="/subjects/%d/delete"]', $subject->getId()))->form());

        self::assertResponseRedirects('/subjects');
        $this->client->followRedirect();
        self::assertSelectorTextContains('.alert-danger', 'Não é possível excluir o assunto "Testes"');
    }
}
