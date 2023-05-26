<?php

namespace OHMedia\SecurityBundle\Twig;

use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class BootstrapExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('bootstrap_alerts', [$this, 'bootstrapAlerts'], [
                'needs_environment' => true,
                'is_safe' => ['html'],
            ]),
        ];
    }

    public function bootstrapAlerts(Environment $env)
    {
        $alertClasses = [
            'primary',
            'secondary',
            'success',
            'danger',
            'warning',
            'info',
            'light',
            'dark',
        ];

        return $env->render('@OHMediaSecurity/bootstrap/alerts.html.twig', [
            'alertClasses' => $alertClasses,
        ]);
    }
}
