<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Author;
use App\Entity\Book;
use App\Entity\Subject;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

final class AppFixtures extends Fixture
{
    private const array AUTHORS = [
        'Machado de Assis',
        'Clarice Lispector',
        'Robert C. Martin',
        'Martin Fowler',
        'Kent Beck',
        'Erich Gamma',
        'Richard Helm',
        'Eric Evans',
    ];

    private const array SUBJECTS = [
        'Romance',
        'Literatura Nacional',
        'Eng. de Software',
        'Arquitetura',
        'Testes',
        'Padrões de Projeto',
    ];

    private const array BOOKS = [
        ['Dom Casmurro', 'Garnier', 1, '1899', '39.90', ['Machado de Assis'], ['Romance', 'Literatura Nacional']],
        ['Memórias Póstumas de Brás Cubas', 'Tipografia Nacional', 2, '1881', '42.50', ['Machado de Assis'], ['Romance', 'Literatura Nacional']],
        ['A Hora da Estrela', 'Rocco', 1, '1977', '34.90', ['Clarice Lispector'], ['Romance', 'Literatura Nacional']],
        ['Clean Code', 'Prentice Hall', 1, '2008', '189.90', ['Robert C. Martin'], ['Eng. de Software']],
        ['Clean Architecture', 'Prentice Hall', 1, '2017', '159.00', ['Robert C. Martin'], ['Eng. de Software', 'Arquitetura']],
        ['Refactoring', 'Addison-Wesley', 2, '2018', '249.90', ['Martin Fowler', 'Kent Beck'], ['Eng. de Software', 'Testes']],
        ['Test-Driven Development', 'Addison-Wesley', 1, '2002', '199.00', ['Kent Beck'], ['Testes']],
        ['Design Patterns', 'Addison-Wesley', 1, '1994', '279.90', ['Erich Gamma', 'Richard Helm'], ['Padrões de Projeto', 'Eng. de Software']],
        ['Domain-Driven Design', 'Addison-Wesley', 1, '2003', '1234.56', ['Eric Evans'], ['Arquitetura', 'Eng. de Software']],
    ];

    public function load(ObjectManager $manager): void
    {
        $authors = [];
        foreach (self::AUTHORS as $name) {
            $manager->persist($authors[$name] = new Author($name));
        }

        $subjects = [];
        foreach (self::SUBJECTS as $description) {
            $manager->persist($subjects[$description] = new Subject($description));
        }

        foreach (self::BOOKS as [$title, $publisher, $edition, $year, $price, $authorNames, $subjectDescriptions]) {
            $book = (new Book())
                ->setTitle($title)
                ->setPublisher($publisher)
                ->setEdition($edition)
                ->setPublicationYear($year)
                ->setPrice($price);

            foreach ($authorNames as $name) {
                $book->addAuthor($authors[$name]);
            }

            foreach ($subjectDescriptions as $description) {
                $book->addSubject($subjects[$description]);
            }

            $manager->persist($book);
        }

        $manager->flush();
    }
}
