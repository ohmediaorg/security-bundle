<?php

namespace OHMedia\SecurityBundle\Service;

class EntityChoiceManager
{
    private array $entityChoices = [];

    public function addEntityChoice(EntityChoiceInterface $entityChoice)
    {
        $this->entityChoices[] = $entityChoice;
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
