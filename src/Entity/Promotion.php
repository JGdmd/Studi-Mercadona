<?php

namespace App\Entity;

use App\Repository\PromotionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use DateTimeImmutable;

#[ORM\Entity(repositoryClass: PromotionRepository::class)]
class Promotion
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    #[Assert\Range(
        min: 1,
        max: 99,
        notInRangeMessage: 'Le pourcentage de promotion doit être compris entre 1% et 99%',
    )]
    #[Assert\NotBlank(
        message: "Le '{{label}}' ne peut être vide."
    )]
    private float $discount;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    /**
     * @var string A "Y-m-d" formatted value
     * */
    private $begins;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    /**
     * @var string A "Y-m-d" formatted value
     * */
    private $ends;

    #[ORM\ManyToOne(inversedBy: 'promotions')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotBlank(
        message: "Le '{{label}}' ne peut être vide."
    )]
    private Product $product;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDiscount(): ?float
    {
        return $this->discount;
    }

    public function setDiscount(float $discount): static
    {
        $this->discount = $discount;

        return $this;
    }

    public function getBegins(): ?\DateTimeInterface
    {
        
        return $this->begins;
    }

    public function setBegins(\DateTimeInterface $begins): static
    {
        $this->begins = DateTimeImmutable::createFromMutable($begins);

        return $this;
    }

    public function getEnds(): ?\DateTimeInterface
    {
        return $this->ends;
    }

    public function setEnds(\DateTimeInterface $ends): static
    {
        $this->ends = DateTimeImmutable::createFromMutable($ends);

        return $this;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): static
    {
        $this->product = $product;

        return $this;
    }
}
