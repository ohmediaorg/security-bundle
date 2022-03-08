<?php

namespace OHMedia\SecurityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use OHMedia\SecurityBundle\Entity\Traits\Blameable;

#[ORM\MappedSuperclass]
abstract class Entity
{
    use Blameable;

    #[ORM\Id()]
    #[ORM\GeneratedValue()]
    #[ORM\Column(type: 'integer')]
    protected $id;

    public function __clone()
    {
        $this->id = null;
        $this->created_at = null;
        $this->created_by = null;
        $this->updated_at = null;
        $this->updated_by = null;
    }

    public function getId(): ?int
    {
        return $this->id;
    }
}
