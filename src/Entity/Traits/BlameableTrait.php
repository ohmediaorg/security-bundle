<?php

namespace OHMedia\SecurityBundle\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;

trait BlameableTrait
{
    #[ORM\Column(type: 'datetime', nullable: true)]
    protected $created_at;

    #[ORM\Column(type: 'string', length: 180, nullable: true)]
    protected $created_by;

    #[ORM\Column(type: 'datetime', nullable: true)]
    protected $updated_at;

    #[ORM\Column(type: 'string', length: 180, nullable: true)]
    protected $updated_by;

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->created_at;
    }

    public function setCreatedAt(?\DateTimeInterface $createdAt): self
    {
        $this->created_at = $createdAt;

        return $this;
    }

    public function getCreatedBy(): ?string
    {
        return $this->created_by;
    }

    public function setCreatedBy(?string $createdBy): self
    {
        $this->created_by = $createdBy;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): self
    {
        $this->updated_at = $updatedAt;

        return $this;
    }

    public function getUpdatedBy(): ?string
    {
        return $this->updated_by;
    }

    public function setUpdatedBy(?string $updatedBy): self
    {
        $this->updated_by = $updatedBy;

        return $this;
    }
}
