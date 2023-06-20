<?php

namespace OHMedia\SecurityBundle\Controller;

use OHMedia\UtilityBundle\Util\RandomString;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class PasswordController extends AbstractController
{
    #[Route('/forgot-password', name: 'user_forgot_password')]
    public function forgotPassword(Request $request, UserRepository $userRepository): Response
    {
        $form = $this->createFormBuilder()
            ->add('email', EmailType::class)
            ->add('submit', SubmitType::class)
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $email = $form->get('email')->getData();

            $user = $userRepository->findOneByEmail($email);

            if (!$user) {
                $this->addFlash('warning', 'We could not find a user with that email address.');

                return $this->redirectToRoute('user_forgot_password');
            }

            $currentReset = $user->getNextReset();

            $now = new \DateTime();

            if ($currentReset && $currentReset > $now) {
                $this->addFlash('warning', 'You recently requested a password reset.');

                return $this->redirectToRoute('user_forgot_password');
            }

            $token = RandomString::get(25, function($token) {
                return !$userRepository->findOneByResetToken($token);
            });

            $nextReset = new \DateTime('+1 hour');
            $resetExpires = new \DateTime('+2 hours');

            $user
                ->setResetToken($token)
                ->setNextReset($nextReset)
                ->setResetExpires($resetExpires)
            ;

            $userRepository->save($user, true);

            $url = $this->generateUrl('user_password_reset', [
                'token' => $token,
            ]);

            // TODO: email

            $this->addFlash('notice', 'Check your email for a link to reset your password.');

            return $this->redirectToRoute('user_forgot_password');
        }

        return $this->render('@OHMediaSecurity/forgot-password.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/password-reset/{token}', name: 'user_password_reset')]
    public function passwordReset(
        UserPasswordHasherInterface $passwordHasher,
        Request $request,
        UserRepository $userRepository,
        string $token
    ): Response
    {
        $user = $token ? $userRepository->findOneByResetToken($token) : null;

        if (!$user) {
            $this->addFlash('error', 'Invalid password reset token.');

            return $this->redirectToRoute('user_forgot_password');
        }

        $now = new \DateTime();

        if ($now > $user->getResetExpires()) {
            $this->addFlash('error', 'This password reset is expired. Please start a new one.');

            return $this->redirectToRoute('user_forgot_password');
        }

        $form = $this->createFormBuilder()
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'options' => ['attr' => ['autocomplete' => 'new-password']],
                'invalid_message' => 'The password fields must match.',
                'first_options'  => ['label' => 'New Password'],
                'second_options' => ['label' => 'Repeat Password'],
            ])
            ->add('submit', SubmitType::class)
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $password = $form->get('password')->getData();

            $hashedPassword = $passwordHasher->hashPassword(
                $user,
                $password
            );

            $user
                ->setPassword($hashedPassword)
                ->setResetToken(null)
                ->setNextReset(null)
                ->setResetExpires(null)
            ;

            $userRepository->save($user, true);

            $this->addFlash('notice', 'Your password was reset successfully.');

            // TODO: redirect to login
            return $this->redirectToRoute('user_forgot_password');
        }

        return $this->render('@OHMediaSecurity/forgot-password.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
