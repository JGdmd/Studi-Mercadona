<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
#[UniqueEntity(fields: ['label'], message: 'Ce produit existe déjà')]
class Product
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
        max: 55,
        maxMessage: 'Le label ne peut être que de 55 caractères maximum',
    )]
    private string $label;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(
        message: "Le '{{label}}' ne peut être vide."
    )]
    #[Assert\Length(
        max: 255,
        maxMessage: 'La description produit ne peut être que de 255 caractères maximum',
    )]
    private string $description;
    
    #[ORM\Column]
    #[Assert\NotBlank(
        message: "Le '{{label}}' ne peut être vide."
    )]
    #[Assert\Range(
        min: 0.01,
        notInRangeMessage: 'Un produit ne peut être en dessous de 0,01€'
    )]
    private float $price;
    
    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotBlank(
        message: "Le '{{label}}' ne peut être vide."
    )]
    private Category $category;
    
    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(
        message: "Le '{{label}}' ne peut être vide."
    )]
    private string $image;


    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotBlank(
        message: "Le '{{label}}' ne peut être vide."
    )]
    private Unit $unit;

    #[ORM\OneToMany(mappedBy: 'product', targetEntity: Promotion::class)]
    private Collection $promotions;

    public function __construct()
    {
        $this->promotions = new ArrayCollection();
    }

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
        $this->label = $label;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): static
    {
        $this->category = $category;

        return $this;
    }

    public function getImage()
    {
        return 'products/' . $this->image;
    }

    public function setImage($image): static
    {
        $this->image = $image;

        return $this;
    }

    public function getUnit(): ?Unit
    {
        return $this->unit;
    }

    public function setUnit(?Unit $unit): static
    {
        $this->unit = $unit;

        return $this;
    }

    /**
     * @return Collection<int, Promotion>
     */
    public function getPromotions(): Collection
    {
        return $this->promotions;
    }

    public function addPromotion(Promotion $promotion): static
    {
        if (!$this->promotions->contains($promotion)) {
            $this->promotions->add($promotion);
            $promotion->setProduct($this);
        }

        return $this;
    }

    public function removePromotion(Promotion $promotion): static
    {
        if ($this->promotions->removeElement($promotion)) {

            if ($promotion->getProduct() === $this) {
                $promotion->setProduct(null);
            }
        }

        return $this;
    }

    public function __toString()
    {
        return $this->label;
    }
}
