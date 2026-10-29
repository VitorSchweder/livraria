<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\BookRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ORM\Entity(repositoryClass: BookRepository::class)]
#[ORM\Table(name: 'livro')]
#[ORM\Index(name: 'livro_titulo_idx', columns: ['titulo'])]
class Book
{
    public const int MIN_PUBLICATION_YEAR = 1450;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(name: 'codl', type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(name: 'titulo', length: 40)]
    #[Assert\NotBlank(message: 'Informe o título.', normalizer: 'trim')]
    #[Assert\Length(max: 40, maxMessage: 'O título deve ter no máximo {{ limit }} caracteres.')]
    private ?string $title = null;

    #[ORM\Column(name: 'editora', length: 40)]
    #[Assert\NotBlank(message: 'Informe a editora.', normalizer: 'trim')]
    #[Assert\Length(max: 40, maxMessage: 'A editora deve ter no máximo {{ limit }} caracteres.')]
    private ?string $publisher = null;

    #[ORM\Column(name: 'edicao', type: Types::INTEGER)]
    #[Assert\NotNull(message: 'Informe a edição.')]
    #[Assert\Positive(message: 'A edição deve ser maior que zero.')]
    #[Assert\LessThanOrEqual(value: 999, message: 'A edição deve ser no máximo {{ compared_value }}.')]
    private ?int $edition = null;

    #[ORM\Column(name: 'anopublicacao', length: 4)]
    #[Assert\NotBlank(message: 'Informe o ano de publicação.')]
    #[Assert\Regex(pattern: '/^\d{4}$/', message: 'O ano de publicação deve ter 4 dígitos (ex.: 2024).')]
    private ?string $publicationYear = null;

    #[ORM\Column(name: 'valor', type: Types::DECIMAL, precision: 10, scale: 2)]
    #[Assert\NotBlank(message: 'Informe o valor.')]
    #[Assert\PositiveOrZero(message: 'O valor não pode ser negativo.')]
    #[Assert\LessThan(value: 100000000, message: 'O valor deve ser menor que R$ 100.000.000,00.')]
    private ?string $price = null;

    /** @var Collection<int, Author> */
    #[ORM\ManyToMany(targetEntity: Author::class)]
    #[ORM\JoinTable(name: 'livro_autor')]
    #[ORM\JoinColumn(name: 'livro_codl', referencedColumnName: 'codl', nullable: false, onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(name: 'autor_codau', referencedColumnName: 'codau', nullable: false, onDelete: 'RESTRICT')]
    #[ORM\OrderBy(['name' => 'ASC'])]
    #[Assert\Count(min: 1, minMessage: 'Selecione ao menos um autor.')]
    private Collection $authors;

    /** @var Collection<int, Subject> */
    #[ORM\ManyToMany(targetEntity: Subject::class)]
    #[ORM\JoinTable(name: 'livro_assunto')]
    #[ORM\JoinColumn(name: 'livro_codl', referencedColumnName: 'codl', nullable: false, onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(name: 'assunto_codas', referencedColumnName: 'codas', nullable: false, onDelete: 'RESTRICT')]
    #[ORM\OrderBy(['description' => 'ASC'])]
    #[Assert\Count(min: 1, minMessage: 'Selecione ao menos um assunto.')]
    private Collection $subjects;

    public function __construct()
    {
        $this->authors = new ArrayCollection();
        $this->subjects = new ArrayCollection();
    }

    #[Assert\Callback]
    public function validatePublicationYear(ExecutionContextInterface $context): void
    {
        if (empty($this->publicationYear) || preg_match('/^\d{4}$/', $this->publicationYear) !== 1) {
            return;
        }

        $year = (int) $this->publicationYear;
        $maxYear = (int) date('Y') + 1;

        if ($year < self::MIN_PUBLICATION_YEAR || $year > $maxYear) {
            $context->buildViolation('O ano de publicação deve estar entre {{ min }} e {{ max }}.')
                ->setParameter('{{ min }}', (string) self::MIN_PUBLICATION_YEAR)
                ->setParameter('{{ max }}', (string) $maxYear)
                ->atPath('publicationYear')
                ->addViolation();
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): self
    {
        $this->title = $title === null ? null : trim($title);

        return $this;
    }

    public function getPublisher(): ?string
    {
        return $this->publisher;
    }

    public function setPublisher(?string $publisher): self
    {
        $this->publisher = $publisher === null ? null : trim($publisher);

        return $this;
    }

    public function getEdition(): ?int
    {
        return $this->edition;
    }

    public function setEdition(?int $edition): self
    {
        $this->edition = $edition;

        return $this;
    }

    public function getPublicationYear(): ?string
    {
        return $this->publicationYear;
    }

    public function setPublicationYear(?string $publicationYear): self
    {
        $this->publicationYear = $publicationYear;

        return $this;
    }

    public function getPrice(): ?string
    {
        return $this->price;
    }

    public function setPrice(?string $price): self
    {
        $this->price = $price;

        return $this;
    }

    /** @return Collection<int, Author> */
    public function getAuthors(): Collection
    {
        return $this->authors;
    }

    public function addAuthor(Author $author): self
    {
        if (!$this->authors->contains($author)) {
            $this->authors->add($author);
        }

        return $this;
    }

    public function removeAuthor(Author $author): self
    {
        $this->authors->removeElement($author);

        return $this;
    }

    /** @return Collection<int, Subject> */
    public function getSubjects(): Collection
    {
        return $this->subjects;
    }

    public function addSubject(Subject $subject): self
    {
        if (!$this->subjects->contains($subject)) {
            $this->subjects->add($subject);
        }

        return $this;
    }

    public function removeSubject(Subject $subject): self
    {
        $this->subjects->removeElement($subject);

        return $this;
    }
}
