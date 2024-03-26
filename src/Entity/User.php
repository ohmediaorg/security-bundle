<?php

namespace OHMedia\SecurityBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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

    #[ORM\Id()]
    #[ORM\GeneratedValue()]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 180, unique: true)]
    private $email;

    #[ORM\Column(type: 'string')]
    private $password;

    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    private $first_name;

    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    private $last_name;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private $developer;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private $enabled;

    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    private $reset_token;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private $next_reset;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private $reset_expires;

    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    private $verify_token;

    #[ORM\Column(type: 'string', length: 180, nullable: true)]
    private $verify_email;

    #[ORM\ManyToMany(targetEntity: UserRole::class, inversedBy: 'users')]
    private $user_roles;

    public function __construct()
    {
        $this->user_roles = new ArrayCollection();
    }

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

    public function setEmail(string $email): self
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

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->first_name;
    }

    public function setFirstName(?string $firstName): self
    {
        $this->first_name = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->last_name;
    }

    public function setLastName(?string $lastName): self
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

    public function setDeveloper(?bool $developer): self
    {
        $this->developer = $developer;

        return $this;
    }

    public function isEnabled(): ?bool
    {
        return $this->enabled;
    }

    public function setEnabled(?bool $enabled): self
    {
        $this->enabled = $enabled;

        return $this;
    }

    public function getResetToken(): ?string
    {
        return $this->reset_token;
    }

    public function setResetToken(?string $resetToken): self
    {
        $this->reset_token = $resetToken;

        return $this;
    }

    public function getNextReset(): ?\DateTimeImmutable
    {
        return $this->next_reset;
    }

    public function setNextReset(?\DateTimeImmutable $nextReset): self
    {
        $this->next_reset = $nextReset;

        return $this;
    }

    public function getResetExpires(): ?\DateTimeImmutable
    {
        return $this->reset_expires;
    }

    public function setResetExpires(?\DateTimeImmutable $resetExpires): self
    {
        $this->reset_expires = $resetExpires;

        return $this;
    }

    public function getVerifyToken(): ?string
    {
        return $this->verify_token;
    }

    public function setVerifyToken(?string $verifyToken): self
    {
        $this->verify_token = $verifyToken;

        return $this;
    }

    public function getVerifyEmail(): ?string
    {
        return $this->verify_email;
    }

    public function setVerifyEmail(?string $verifyEmail): self
    {
        $this->verify_email = $verifyEmail;

        return $this;
    }

    /**
     * @return Collection[]
     */
    public function getUserRoles(): Collection
    {
        return $this->user_roles;
    }

    public function addUserRole(UserRole $userRole): self
    {
        if (!$this->user_roles->contains($userRole)) {
            $this->user_roles[] = $userRole;
        }

        return $this;
    }

    public function removeUserRole(UserRole $userRole): self
    {
        if ($this->user_roles->contains($userRole)) {
            $this->user_roles->removeElement($userRole);
        }

        return $this;
    }

    public function hasUserRole($name)
    {
        foreach ($this->user_roles as $userRole) {
            if ($userRole->getName() === $name) {
                return true;
            }
        }

        return false;
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
}
