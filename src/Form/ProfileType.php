<?php

namespace OHMedia\SecurityBundle\Form;

use OHMedia\SecurityBundle\Entity\User;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimezoneType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\PasswordStrength;

class ProfileType extends AbstractType
{
    public function __construct(
        #[Autowire('%oh_media_timezone.timezone%')]
        private string $defaultTimezone,
        #[Autowire('%oh_media_security.password_strength%')]
        private int $passwordStrength,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $user = isset($options['data']) ? $options['data'] : null;

        $verifyEmail = $user ? $user->getVerifyEmail() : null;

        $userExists = $user && $user->getId();

        $passwordLabel = $userExists ? 'Change Password' : 'Password';

        $passwordConstraints = [];

        if (!$userExists) {
            $passwordConstraints[] = new NotBlank();
        }

        if ($this->passwordStrength) {
            $passwordConstraints[] = new PasswordStrength([
                'minScore' => $this->passwordStrength,
            ]);
        }

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
                'constraints' => $passwordConstraints,
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
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
