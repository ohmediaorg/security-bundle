<?php

namespace OHMedia\SecurityBundle\Controller;

use OHMedia\AntispamBundle\Form\Type\RecaptchaType;
use OHMedia\EmailBundle\Entity\Email;
use OHMedia\EmailBundle\Repository\EmailRepository;
use OHMedia\EmailBundle\Util\EmailAddress;
use OHMedia\SecurityBundle\Entity\User;
use OHMedia\SecurityBundle\Repository\UserRepository;
use OHMedia\UtilityBundle\Util\RandomString;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class PasswordController extends AbstractController
{
    #[Route('/register', name: 'user_register')]
    public function forgotPassword(
        EmailRepository $emailRepository,
        Request $request,
        UserRepository $userRepository
    ): Response
    {
        if ($this->getUser()) {
            $this->addFlash('warning', 'You are already logged in.');

            return $this->redirectToRoute('user_index');
        }

        $user = new User();

        $form = $this->createForm(RegisterType::class, $user, [
            'honeypot_protection' => true,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $email = $form->get('email')->getData();

            $exists = $userRepository->findOneByEmail($email);

            if ($exists) {
                $this->addFlash('error', 'This email is already registered.');

                return $this->redirectToRoute('user_register');
            }

            $fakeEmail = RandomString::get(30, function($token) use ($userRepository) {
                return !$userRepository->findOneBy([
                    'email' => $token,
                ]);
            });

            $hashedPassword = $passwordHasher->hashPassword(
                $user,
                $form->get('password')->getData()
            );

            $token = RandomString::get(50, function($token) use ($userRepository) {
                return !$userRepository->findOneBy([
                    'verify_token' => $token,
                ]);
            });

            $user
                ->setEmail($fakeEmail)
                ->setPassword($hashedPassword)
                ->setVerifyToken($token)
                ->setVerifyEmail($email)
            ;

            $userRepository->save($user, true);

            $this->createConfirmationEmail($emailRepository, $user);

            $this->addFlash('notice', 'Check your inbox for a verification email to complete your registration.');

            return $this->redirectToRoute('user_register');
        }

        return $this->render('@OHMediaSecurity/register_form.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    private function createConfirmationEmail(EmailRepository $emailRepository, User $user)
    {
        $url = $this->generateUrl('user_verify_email', [
            'token' => $user->getVerifyToken(),
        ], UrlGeneratorInterface::ABSOLUTE_URL);

        $to = new EmailAddress($user->getVerifyEmail(), $user->getFullName());

        $email = (new Email())
            ->setSubject('Registration Confirmation')
            ->setTemplate('@OHMediaSecurity/register_confirm_email.html.twig', [
                'user' => $user,
                'url' => $url,
            ])
            ->setTo($to)
        ;

        $emailRepository->save($email, true);
    }

    #[Route('/password-reset/{token}', name: 'user_password_reset')]
    public function passwordReset(
        UserPasswordHasherInterface $passwordHasher,
        Request $request,
        UserRepository $userRepository,
        string $token
    ): Response
    {
        $user = $token
            ? $userRepository->findOneBy([
                'reset_token' => $token,
            ])
            : null;

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

            return $this->redirectToRoute('user_login');
        }

        return $this->render('@OHMediaSecurity/password_reset_form.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
