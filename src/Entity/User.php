<?php

namespace OHMedia\SecurityBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use OHMedia\SecurityBundle\Repository\UserRepository;
use OHMedia\TimezoneBundle\Entity\Traits\TimezoneUserTrait;
use OHMedia\UtilityBundle\Entity\BlameableEntityTrait;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[UniqueEntity('email')]
#[UniqueEntity(fields: 'verify_email', errorPath: 'email')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    use BlameableEntityTrait;
    use TimezoneUserTrait;

    public const TYPE_DEVELOPER = 'developer';
    public const TYPE_SUPER = 'super';
    public const TYPE_ADMIN = 'admin';

    public const PASSWORD_CHANGE = 'PASSWORD_CHANGE';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 180)]
    private ?string $email = null;

    #[ORM\Column]
    private string $password;

    #[ORM\Column(length: 50, nullable: true)]
    #[Assert\Length(max: 50)]
    private ?string $first_name = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Assert\Length(max: 50)]
    private ?string $last_name = null;

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

    #[ORM\Column(length: 255)]
    private ?string $type = null;

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

    public function setEmail(?string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getUsername(): string
    {
        return $this->getEmail();
    }

    private ?string $new_password = null;

    public function getNewPassword(): ?string
    {
        return $this->new_password;
    }

    public function setNewPassword(?string $new_password): static
    {
        if ($new_password) {
            $this->password = self::PASSWORD_CHANGE;
        }

        $this->new_password = $new_password;

        return $this;
    }

    public function getPassword(): string
    {
        return (string) $this->password;
    }

    public function setPassword(?string $password): static
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

    private bool $sendVerifyEmail = false;

    public function shouldSendVerifyEmail(): bool
    {
        return $this->sendVerifyEmail;
    }

    public function doEmailVerification(string $oldEmail, string $newEmail, string $verifyToken): static
    {
        $this->sendVerifyEmail = true;
        $this->email = $oldEmail;
        $this->verify_email = $newEmail;
        $this->verify_token = $verifyToken;

        return $this;
    }

    private bool $emailJustVerified = false;

    public function wasEmailJustVerified(): bool
    {
        return $this->emailJustVerified;
    }

    public function setEmailVerified(): static
    {
        if (!$this->verify_email || !$this->verify_token) {
            throw new \LogicException('The verify email/token is blank.');
        }

        $this->emailJustVerified = true;
        $this->email = $this->verify_email;
        $this->verify_token = null;
        $this->verify_email = null;

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
        $this->new_password = null;
    }

    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function isType(string $type): bool
    {
        return $type === $this->type;
    }

    public function isTypeDeveloper(): bool
    {
        return $this->isType(static::TYPE_DEVELOPER);
    }

    public function isTypeSuper(): bool
    {
        return $this->isType(static::TYPE_SUPER);
    }

    public function isTypeAdmin(): bool
    {
        return $this->isType(static::TYPE_ADMIN);
    }

    public function isAdmin(): bool
    {
        return $this->isTypeDeveloper()
            || $this->isTypeSuper()
            || $this->isTypeAdmin();
    }
}
