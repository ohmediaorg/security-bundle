<?php

namespace OHMedia\SecurityBundle\Controller;

use OHMedia\AntispamBundle\Form\Type\RecaptchaType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class LoginController extends AbstractController
{
    #[Route('/login', name: 'user_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        $formBuilder = $this->createFormBuilder(null, [
            'csrf_protection' => true,
            'csrf_field_name' => '_csrf_token',
            'csrf_token_id' => 'authenticate',
            'honeypot_protection' => true,
        ]);

        $form = $formBuilder
            ->add('_username', EmailType::class, [
                'label' => 'Email',
                'data' => $authenticationUtils->getLastUsername(),
            ])
            ->add('_password', PasswordType::class, [
                'help' => '<a href="/forgot-password">Forgot your password?</a>',
                'help_html' => true,
            ])
            ->add('recaptcha', RecaptchaType::class)
            ->add('submit', SubmitType::class)
            ->getForm();

        $error = $authenticationUtils->getLastAuthenticationError();

        if ($error) {
            $this->addFlash('error', $error->getMessage());
        }

        return $this->render('@OHMediaSecurity/login_form.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/logout', name: 'user_logout', methods: ['GET'])]
    public function logout(): Response
    {
    }
}
