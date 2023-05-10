<?php

namespace App\Form;

use App\Entity\UserRole;
use OHMedia\SecurityBundle\Provider\ProviderHandler;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserRoleType extends AbstractType
{
    private $handler;

    public function __construct(ProviderHandler $handler)
    {
        $this->handler = $handler;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $choices = [];

        foreach ($this->handler->getEntityActions() as $action) {
            $choices = array_merge($choices, $action['actions']);
        }

        $builder
            ->add('name')
            ->add('actions', ChoiceType::class, [
                'multiple' => true,
                'expanded' => true,
                'choices' => $choices,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => UserRole::class,
        ]);
    }
}
