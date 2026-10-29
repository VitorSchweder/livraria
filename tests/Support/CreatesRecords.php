<?php

declare(strict_types=1);

namespace App\Tests\Support;

use App\Entity\Author;
use App\Entity\Book;
use App\Entity\Subject;
use Doctrine\ORM\EntityManagerInterface;

trait CreatesRecords
{
    protected function entityManager(): EntityManagerInterface
    {
        return static::getContainer()->get(EntityManagerInterface::class);
    }

    protected function createAuthor(string $name): Author
    {
        $author = new Author($name);
        $this->entityManager()->persist($author);
        $this->entityManager()->flush();

        return $author;
    }

    protected function createSubject(string $description): Subject
    {
        $subject = new Subject($description);
        $this->entityManager()->persist($subject);
        $this->entityManager()->flush();

        return $subject;
    }

    /**
     * @param list<Author>  $authors
     * @param list<Subject> $subjects
     */
    protected function createBook(string $title, array $authors, array $subjects, string $price = '50.00'): Book
    {
        $book = (new Book())
            ->setTitle($title)
            ->setPublisher('Editora Teste')
            ->setEdition(1)
            ->setPublicationYear('2020')
            ->setPrice($price);

        foreach ($authors as $author) {
            $book->addAuthor($author);
        }

        foreach ($subjects as $subject) {
            $book->addSubject($subject);
        }

        $this->entityManager()->persist($book);
        $this->entityManager()->flush();

        return $book;
    }
}
