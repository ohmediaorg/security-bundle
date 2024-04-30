<?php

namespace OHMedia\SecurityBundle\EventListener;

use Doctrine\ORM\Event\PreUpdateEventArgs;
use OHMedia\SecurityBundle\Service\Blamer;

class BlamePreUpdate
{
    public function __construct(private Blamer $blamer)
    {
    }

    public function preUpdate(PreUpdateEventArgs $args)
    {
        $this->blamer->blame($args->getObject());
    }
}
