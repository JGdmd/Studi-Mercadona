<?php

namespace App\Entity;

use App\Repository\CategoryRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CategoryRepository::class)]
#[UniqueEntity(fields: ['label'], message: 'Cette catégorie existe déjà.')]
class Category
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 55, unique: true)]
    #[Assert\NotBlank(
        message: "Le '{{label}}' ne peut être vide."
    )]
    #[Assert\Length(
        max: 50,
        maxMessage: 'Le label ne peut être que de 50 caractères maximum',
    )]
    private string $label;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(string $label): static
    {
        $this->label = strtolower($label);
        return $this;
    }
    
    public function __toString()
    {
        return $this->label;
    }
}
