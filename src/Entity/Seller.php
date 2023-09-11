<?php

namespace App\Entity;

use App\Repository\SellerRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SellerRepository::class)]
#[UniqueEntity(fields: ['code'], message: 'Ce code est déjà utilisé.')]
class Seller implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 13, unique: true)]
    #[Assert\NotBlank(
        message: '{{ label }} ne peut être vide'
    )]
    #[Assert\Length(
        exactly: 13,
        exactMessage: 'Le code employé est de 13 caractères'
    )]
    #[Assert\Regex(
        pattern: '/^MER-[0-9]{5}-[0-9]{3}$/',
        match: true,
        message: 'Ce code n\'est pas un code employé',
    )]
    private string $code;

    #[ORM\Column]
    private array $roles = ['ROLE_ADMIN'];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    #[Assert\NotBlank(
        message: "Le '{{label}}' ne peut être vide."
    )]
    private string $password;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(
        message: "Le '{{label}}' ne peut être vide."
    )]
    #[Assert\Length(
        max: 255,
        maxMessage: 'Adresse email trop longue'
    )]
    #[Assert\Email(
        message: 'Le mail {{ value }} n\'est pas une adresse valide.',
    )]
    private string $email;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): static
    {
        $this->code = $code;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->code;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;

        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }
}
