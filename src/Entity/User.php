<?php

namespace OHMedia\SecurityBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\MappedSuperclass
 */
abstract class User extends Entity implements UserInterface
{
    /**
     * @ORM\Column(type="string", length=180, unique=true)
     */
    protected $email;

    /**
     * @ORM\Column(type="string")
     */
    protected $password;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $developer;

    /**
     * @ORM\Column(type="string", length=64, nullable=true)
     */
    protected $timezone;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $enabled;

    /**
     * @ORM\ManyToMany(targetEntity="\OHMedia\SecurityBundle\Entity\UserRole", inversedBy="users")
     */
    protected $user_roles;

    public function __construct()
    {
        $this->user_roles = new ArrayCollection();
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
    
    public function getTimezone(): string
    {
        return (string) $this->timezone;
    }

    public function setTimezone(?string $timezone): self
    {
        $this->timezone = $timezone;

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

    /**
     * @return Collection[]
     */
    public function getUserRoles(): Collection
    {
        return $this->user_roles;
    }

    public function addUserRole(UserRole $user_role): self
    {
        if (!$this->user_roles->contains($user_role)) {
            $this->user_roles[] = $user_role;
        }

        return $this;
    }

    public function removeUserRole(UserRole $user_role): self
    {
        if ($this->user_roles->contains($user_role)) {
            $this->user_roles->removeElement($user_role);
        }

        return $this;
    }
    
    public function hasUserRole($name)
    {
        foreach ($this->user_roles as $user_role) {
            if ($user_role->getName() === $name) {
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
    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }
}
