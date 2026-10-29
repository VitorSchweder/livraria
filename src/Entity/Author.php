<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\AuthorRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: AuthorRepository::class)]
#[ORM\Table(name: 'autor')]
#[ORM\Index(name: 'autor_nome_idx', columns: ['nome'])]
class Author
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(name: 'codau', type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(name: 'nome', length: 40)]
    #[Assert\NotBlank(message: 'Informe o nome do autor.', normalizer: 'trim')]
    #[Assert\Length(max: 40, maxMessage: 'O nome deve ter no máximo {{ limit }} caracteres.')]
    private ?string $name = null;

    public function __construct(?string $name = null)
    {
        $this->name = $name;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name === null ? null : trim($name);

        return $this;
    }

    public function __toString(): string
    {
        return (string) $this->name;
    }
}
