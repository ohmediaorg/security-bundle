<?php

namespace OHMedia\SecurityBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use OHMedia\SecurityBundle\Entity\Traits\BlameableTrait;
use Stringable;

#[ORM\Entity]
class UserRole implements Stringable
{
    use BlameableTrait;

    #[ORM\Id()]
    #[ORM\GeneratedValue()]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 50, unique: true)]
    private $name;

    #[ORM\Column(type: 'json')]
    private $attributes = [];

    #[ORM\Column(type: 'boolean', nullable: true)]
    private $system_generated;

    #[ORM\ManyToMany(targetEntity: User::class, mappedBy: 'user_roles')]
    private $users;

    public function __construct()
    {
        $this->users = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function setAttributes(array $attributes): self
    {
        $this->attributes = $attributes;

        return $this;
    }

    public function getSystemGenerated(): ?bool
    {
        return $this->system_generated;
    }

    public function setSystemGenerated(?bool $systemGenerated): self
    {
        $this->system_generated = $systemGenerated;

        return $this;
    }

    /**
     * @return Collection[]
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users[] = $user;
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        if ($this->users->contains($user)) {
            $this->users->removeElement($user);
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->name;
    }
}
