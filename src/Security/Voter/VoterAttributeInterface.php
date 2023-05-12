<?php

namespace OHMedia\SecurityBundle\Voter;

interface AttributeInterface
{
    public function getName(): string;
    public function getDescription(): string;
    public function vote(Entity $entity, EntityUser $user): bool;
}
