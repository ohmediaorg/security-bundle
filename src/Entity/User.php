<?php

namespace OHMedia\SecurityBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use OHMedia\SecurityBundle\Entity\Traits\Blameable;
use OHMedia\SecurityBundle\Repository\UserRepository;
use OHMedia\TimezoneBundle\Entity\Traits\TimezoneUser;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[UniqueEntity('email')]
class User
implements UserInterface, PasswordAuthenticatedUserInterface
{
    use Blameable;
    use TimezoneUser;

    #[ORM\Id()]
    #[ORM\GeneratedValue()]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 180, unique: true)]
    private $email;

    #[ORM\Column(type: 'string')]
    private $password;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private $developer;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private $enabled;

    #[ORM\Column(type: 'string', length: 25, nullable: true)]
    private $reset_token;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private $next_reset;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    private $reset_expires;

    #[ORM\ManyToMany(targetEntity: UserRole::class, inversedBy: 'users')]
    private $user_roles;

    public function __construct()
    {
        $this->user_roles = new ArrayCollection();
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
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getUserIdentifier(): string
    {
        return $this->email;
    }
}
