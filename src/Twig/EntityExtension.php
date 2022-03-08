<?php

namespace OHMedia\SecurityBundle\Twig;

use OHMedia\SecurityBundle\Entity\Entity;
use LogicException;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class EntityExtension extends AbstractExtension
{
    private $router;
    private $security;
    
    public function __construct(Router $router, AuthorizationCheckerInterface $security)
    {
        $this->router = $router;
        $this->security = $security;
    }
    
    public function getFunctions()
    {
        return [
            new TwigFunction('ohmedia_entity_action', [$this, 'entityAction'], [
                'is_safe' => ['html']
            ])
        ];
    }
    
    public function entityAction($action, $entity, $route, $text, array $attributes = [])
    {
        if (!$entity instanceof Entity) {
            throw new LogicException(sprintf(
                '$entity must be an instance of %s',
                Entity::class
            ));
        }
        
        if (null === $this->security) {
            return null;
        }
        
        if ($this->security->isGranted($action, $entity)) {
            $href = $this->router->generate($route, [
                'id' => $entity->getId(),
                'action' => $action
            ]);
            
            if (!array_key_exists('class', $attributes)) {
                $attributes['class'] = '';
            }
            
            $attributes['class'] .= sprintf(' entity-%s', $action);
            
            $attributes['href'] = $href;
            
            $attributesString = [];
            foreach ($attributes as $attribute => $value) {
                $attributesString[] = sprintf(
                    '%s="%s"', 
                    $attribute,
                    htmlspecialchars($value)
                );
            }
            
            $attributesString = implode(' ', $attributesString);
            
            return "<a $attributesString>$text</a>";
        }
        
        return null;
    }
}
