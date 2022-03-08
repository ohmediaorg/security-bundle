<?php

namespace OHMedia\SecurityBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ActionsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (!isset($options['cancel_options']['attr'])) {
            $options['cancel_options']['attr'] = [];
        }

        if (!isset($options['cancel_options']['attr']['formnovalidate'])) {
            $options['cancel_options']['attr']['formnovalidate'] = '';
        }

        $builder
            ->add('save', SubmitType::class, $options['save_options'])
            ->add('cancel', SubmitType::class, $options['cancel_options'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'mapped' => false,
            'label' => false,
            'save_options' => [],
            'cancel_options' => []
        ]);
    }

    public function getParent(): ?string
    {
        return FormType::class;
    }
}
