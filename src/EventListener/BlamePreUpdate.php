<?php

namespace OHMedia\SecurityBundle\EventListener;

use Doctrine\ORM\Event\PreUpdateEventArgs;
use OHMedia\SecurityBundle\Service\Blamer;

class BlamePreUpdate
{
    private $blamer;

    public function __construct(Blamer $blamer)
    {
        $this->blamer = $blamer;
    }

    public function preUpdate(PreUpdateEventArgs $args)
    {
        $this->blamer->blame($args->getObject());
    }
}
