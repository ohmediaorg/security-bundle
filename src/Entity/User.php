<?php

namespace OHMedia\SecurityBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use OHMedia\SecurityBundle\Entity\Traits\BlameableTrait;
use OHMedia\SecurityBundle\Repository\UserRepository;
use OHMedia\TimezoneBundle\Entity\Traits\TimezoneUserTrait;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[UniqueEntity('email')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    use BlameableTrait;
    use TimezoneUserTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    private ?string $email = null;

    #[ORM\Column]
    private string $password;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $first_name = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $last_name = null;

    #[ORM\Column(nullable: true)]
    private ?bool $developer = null;

    #[ORM\Column(nullable: true)]
    private ?bool $enabled = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $reset_token = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeInterface $next_reset = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeInterface $reset_expires = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $verify_token = null;

    #[ORM\Column(length: 180, nullable: true)]
    private ?string $verify_email = null;

    #[ORM\Column]
    private array $entities = [];

    #[ORM\Column(options: ['default' => false])]
    private ?bool $admin = false;

    public function __toString(): string
    {
        $fullName = $this->getFullName();

        return $fullName ?: $this->email;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): string
    {
        return (string) $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getUsername(): string
    {
        return $this->getEmail();
    }

    public function getPassword(): string
    {
        return (string) $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->first_name;
    }

    public function setFirstName(?string $firstName): static
    {
        $this->first_name = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->last_name;
    }

    public function setLastName(?string $lastName): static
    {
        $this->last_name = $lastName;

        return $this;
    }

    public function getFullName(): string
    {
        $parts = [];

        if ($this->first_name) {
            $parts[] = $this->first_name;
        }

        if ($this->last_name) {
            $parts[] = $this->last_name;
        }

        return implode(' ', $parts);
    }

    public function isDeveloper(): bool
    {
        return (bool) $this->developer;
    }

    public function setDeveloper(?bool $developer): static
    {
        $this->developer = $developer;

        return $this;
    }

    public function isEnabled(): ?bool
    {
        return $this->enabled;
    }

    public function setEnabled(?bool $enabled): static
    {
        $this->enabled = $enabled;

        return $this;
    }

    public function getResetToken(): ?string
    {
        return $this->reset_token;
    }

    public function setResetToken(?string $resetToken): static
    {
        $this->reset_token = $resetToken;

        return $this;
    }

    public function getNextReset(): ?\DateTimeImmutable
    {
        return $this->next_reset;
    }

    public function setNextReset(?\DateTimeImmutable $nextReset): static
    {
        $this->next_reset = $nextReset;

        return $this;
    }

    public function getResetExpires(): ?\DateTimeImmutable
    {
        return $this->reset_expires;
    }

    public function setResetExpires(?\DateTimeImmutable $resetExpires): static
    {
        $this->reset_expires = $resetExpires;

        return $this;
    }

    public function getVerifyToken(): ?string
    {
        return $this->verify_token;
    }

    public function setVerifyToken(?string $verifyToken): static
    {
        $this->verify_token = $verifyToken;

        return $this;
    }

    public function getVerifyEmail(): ?string
    {
        return $this->verify_email;
    }

    public function setVerifyEmail(?string $verifyEmail): static
    {
        $this->verify_email = $verifyEmail;

        return $this;
    }

    public function getEntities(): array
    {
        return $this->entities;
    }

    public function setEntities(array $entities): static
    {
        $this->entities = $entities;

        return $this;
    }

    public function getRoles(): array
    {
        return ['ROLE_USER'];
    }

    /**
     * @see UserInterface
     */
    public function getSalt(): ?string
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    public function isAdmin(): ?bool
    {
        return $this->admin;
    }

    public function setAdmin(?bool $admin): static
    {
        $this->admin = $admin;

        return $this;
    }
}
