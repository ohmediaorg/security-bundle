<?php

namespace OHMedia\SecurityBundle\Form;

use OHMedia\SecurityBundle\Entity\User;
use OHMedia\SecurityBundle\Service\EntityChoiceManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimezoneType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    public function __construct(
        private EntityChoiceManager $entityChoiceManager,
        private string $defaultTimezone
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $user = isset($options['data']) ? $options['data'] : null;

        $loggedIn = $options['logged_in'];

        $verifyEmail = $user ? $user->getVerifyEmail() : null;

        $builder
            ->add('first_name', TextType::class, [
                'required' => false,
            ])
            ->add('last_name', TextType::class, [
                'required' => false,
            ])
            ->add('email', EmailType::class, [
                'help' => $verifyEmail
                    ? 'New email address awaiting verification: '.$verifyEmail
                    : '',
            ])
            ->add('password', RepeatedType::class, [
                'required' => !$user || !$user->getId(),
                'type' => PasswordType::class,
                'options' => ['attr' => ['autocomplete' => 'new-password']],
                'invalid_message' => 'The password fields must match.',
                'first_options' => ['label' => 'Password'],
                'second_options' => ['label' => 'Repeat Password'],
                'mapped' => false,
            ])
            ->add('timezone', TimezoneType::class, [
                'required' => false,
                'help' => 'The default timezone is '.$this->defaultTimezone.'.',
                'placeholder' => 'Default',
                'attr' => [
                    'class' => 'nice-select2',
                    'placeholder' => 'Default',
                ],
            ])
        ;

        $usersMatch = $loggedIn && $user && ($loggedIn === $user);

        if (!$usersMatch) {
            $builder->add('enabled', ChoiceType::class, [
                'choices' => [
                    'Yes' => true,
                    'No' => false,
                ],
                'expanded' => true,
                'row_attr' => [
                    'class' => 'fieldset-nostyle',
                ],
            ]);
        }

        if (!$user->isTypeDeveloper() && !$usersMatch) {
            $superTooltip = '<span data-bs-toggle="tooltip" data-bs-title="Access to all backend areas." class="bi bi-info-circle-fill"></span>';

            $adminTooltip = '<span data-bs-toggle="tooltip" data-bs-title="Access to select backend areas." class="bi bi-info-circle-fill"></span>';

            $builder->add('type', ChoiceType::class, [
                'choices' => [
                    'Super Admin '.$superTooltip => User::TYPE_SUPER,
                    'Admin '.$adminTooltip => User::TYPE_ADMIN,
                ],
                'expanded' => true,
                'row_attr' => [
                    'class' => 'fieldset-nostyle',
                ],
                'label_html' => true,
            ]);

            $this->addEntitiesField($builder);
        }
    }

    private function addEntitiesField(FormBuilderInterface $builder)
    {
        $builder->add('entities', ChoiceType::class, [
            'label' => 'Admin Permissions',
            'choices' => $this->entityChoiceManager->getEntityChoices(),
            'choice_label' => 'label',
            'multiple' => true,
            'expanded' => true,
            'row_attr' => [
                'class' => 'fieldset-nostyle',
                'id' => 'user_entities_container',
            ],
        ]);

        $builder->get('entities')
            ->addModelTransformer(new CallbackTransformer(
                function ($entities) {
                    return $this->entityChoiceManager->transformEntitiesToEntityChoices(...$entities);
                },
                function ($entityChoices) {
                    return $this->entityChoiceManager->transformEntityChoicesToEntities(...$entityChoices);
                }
            ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'logged_in' => null,
        ]);
    }
}
