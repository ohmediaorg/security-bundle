<?php

namespace OHMedia\SecurityBundle\Service;

use OHMedia\FileBundle\Service\FileBrowser;
use OHMedia\FileBundle\Service\FileEntityChoice;

class EntityChoiceManager
{
    private FileBrowser $fileBrowser;
    private array $entityChoices = [];

    public function __construct(FileBrowser $fileBrowser)
    {
        $this->fileBrowser = $fileBrowser;
    }

    public function addEntityChoice(EntityChoiceInterface $entityChoice): self
    {
        if ($entityChoice instanceof FileEntityChoice && !$this->fileBrowser->isEnabled()) {
            return $this;
        }

        $this->entityChoices[] = $entityChoice;

        return $this;
    }

    public function getEntityChoices(): array
    {
        usort($this->entityChoices, function (EntityChoiceInterface $a, EntityChoiceInterface $b) {
            return $a->getLabel() <=> $b->getLabel();
        });

        return $this->entityChoices;
    }

    public function transformEntitiesToEntityChoices(string ...$entities): array
    {
        $entityChoices = [];

        foreach ($this->entityChoices as $entityChoice) {
            $intersect = array_intersect($entities, $entityChoice->getEntities());

            if ($intersect) {
                $entityChoices[] = $entityChoice;
            }
        }

        return $entityChoices;
    }

    public function transformEntityChoicesToEntities(EntityChoiceInterface ...$entityChoices): array
    {
        $entities = [];

        foreach ($entityChoices as $entityChoice) {
            $entities = array_merge($entities, $entityChoice->getEntities());
        }

        return array_unique($entities);
    }
}
