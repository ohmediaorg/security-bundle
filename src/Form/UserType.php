<?php

namespace OHMedia\SecurityBundle\Form;

use OHMedia\SecurityBundle\Entity\User;
use OHMedia\SecurityBundle\Service\EntityChoiceManager;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
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
        #[Autowire('%oh_media_timezone.timezone%')]
        private string $defaultTimezone
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $user = isset($options['data']) ? $options['data'] : null;

        $loggedIn = $options['logged_in'];

        $verifyEmail = $user ? $user->getVerifyEmail() : null;

        $userExists = $user && $user->getId();

        $passwordLabel = $userExists ? 'Change Password' : 'Password';

        $builder
            ->add('first_name', TextType::class, [
                'required' => false,
                'label' => 'First Name',
            ])
            ->add('last_name', TextType::class, [
                'required' => false,
                'label' => 'Last Name',
            ])
            ->add('email', EmailType::class, [
                'help' => $verifyEmail
                    ? 'New email address awaiting verification: '.$verifyEmail
                    : '',
            ])
            ->add('new_password', RepeatedType::class, [
                'required' => !$userExists,
                'type' => PasswordType::class,
                'options' => ['attr' => ['autocomplete' => 'new-password']],
                'invalid_message' => 'The password fields must match.',
                'first_options' => ['label' => $passwordLabel],
                'second_options' => ['label' => 'Repeat Password'],
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
                    'class' => 'fieldset-nostyle mb-3',
                ],
            ]);
        }

        $showPermissions = ($user->isTypeSuper() || $user->isTypeAdmin()) && !$usersMatch;

        if ($showPermissions) {
            $builder->add('type', ChoiceType::class, [
                'choices' => [
                    'Super Admin' => User::TYPE_SUPER,
                    'Admin' => User::TYPE_ADMIN,
                ],
                'expanded' => true,
                'row_attr' => [
                    'class' => 'fieldset-nostyle mb-3',
                ],
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
                'class' => 'fieldset-nostyle mb-3',
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
