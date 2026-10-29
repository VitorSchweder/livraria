<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\SubjectRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SubjectRepository::class)]
#[ORM\Table(name: 'assunto')]
#[ORM\UniqueConstraint(name: 'assunto_descricao_uniq', columns: ['descricao'])]
#[UniqueEntity(fields: ['description'], message: 'Já existe um assunto com esta descrição.')]
class Subject
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(name: 'codas', type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(name: 'descricao', length: 20)]
    #[Assert\NotBlank(message: 'Informe a descrição do assunto.', normalizer: 'trim')]
    #[Assert\Length(max: 20, maxMessage: 'A descrição deve ter no máximo {{ limit }} caracteres.')]
    private ?string $description = null;

    public function __construct(?string $description = null)
    {
        $this->description = $description;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description === null ? null : trim($description);

        return $this;
    }

    public function __toString(): string
    {
        return (string) $this->description;
    }
}
