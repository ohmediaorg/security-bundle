<?php

namespace OHMedia\SecurityBundle\Twig;

use OHMedia\SecurityBundle\Entity\User;
use OHMedia\SecurityBundle\Service\EntityChoiceManager;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class EntityChoiceExtension extends AbstractExtension
{
    public function __construct(private EntityChoiceManager $entityChoiceManager)
    {
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
        } elseif ($user->isAdmin()) {
            $badges[] = [
                'class_name' => 'text-bg-success',
                'text' => 'Admin',
            ];
        } else {
            $entityChoices = $this->entityChoiceManager->transformEntitiesToEntityChoices(...$user->getEntities());

            foreach ($entityChoices as $entityChoice) {
                $badges[] = [
                    'class_name' => 'text-bg-secondary',
                    'text' => $entityChoice->getLabel(),
                ];
            }
        }

        return $twig->render('@OHMediaSecurity/user/_user_permissions.html.twig', [
            'badges' => $badges,
        ]);
    }
}
