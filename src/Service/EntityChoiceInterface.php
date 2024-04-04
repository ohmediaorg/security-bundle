<?php

namespace OHMedia\SecurityBundle\Service;

interface EntityChoiceInterface
{
    public function getLabel(): string;

    public function getEntities(): array;
}
