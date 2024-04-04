<?php

namespace OHMedia\SecurityBundle\Twig;

use OHMedia\SecurityBundle\Entity\User;
use OHMedia\SecurityBundle\Service\EntityChoiceManager;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class EntityChoiceExtension extends AbstractExtension
{
    private EntityChoiceManager $entityChoiceManager;

    public function __construct(EntityChoiceManager $entityChoiceManager)
    {
        $this->entityChoiceManager = $entityChoiceManager;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('user_permissions', [$this, 'userPermissions'], [
                'needs_environment' => true,
                'is_safe' => ['html'],
            ]),
        ];
    }

    public function userPermissions(Environment $twig, User $user): string
    {
        $badges = [];

        if ($user->isDeveloper()) {
            $badges[] = [
                'class_name' => 'text-bg-primary',
                'text' => 'Developer',
            ];
        } else {
            $allEntityChoices = $this->entityChoiceManager->getEntityChoices();

            $entityChoices = $this->entityChoiceManager->transformEntitiesToEntityChoices(...$user->getEntities());

            if (count($allEntityChoices) === count($entityChoices)) {
                $badges[] = [
                    'class_name' => 'text-bg-success',
                    'text' => 'Admin',
                ];
            } else {
                foreach ($entityChoices as $entityChoice) {
                    $badges[] = [
                        'class_name' => 'text-bg-secondary',
                        'text' => $entityChoice->getLabel(),
                    ];
                }
            }
        }

        return $twig->render('@OHMediaSecurity/user/_user_permissions.html.twig', [
            'badges' => $badges,
        ]);
    }
}
