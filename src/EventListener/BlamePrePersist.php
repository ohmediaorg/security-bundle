<?php

namespace OHMedia\SecurityBundle\EventListener;

use Doctrine\ORM\Event\PrePersistEventArgs;
use OHMedia\SecurityBundle\Service\Blamer;

class BlamePrePersist
{
    private $blamer;

    public function __construct(Blamer $blamer)
    {
        $this->blamer = $blamer;
    }

    public function prePersist(PrePersistEventArgs $args)
    {
        $this->blamer->blame($args->getObject());
    }
}
