<?php

namespace OHMedia\SecurityBundle\Controller;

use OHMedia\AntispamBundle\Form\Type\CaptchaType;
use OHMedia\BackendBundle\Routing\Attribute\Admin;
use OHMedia\EmailBundle\Entity\Email;
use OHMedia\EmailBundle\Repository\EmailRepository;
use OHMedia\EmailBundle\Util\EmailAddress;
use OHMedia\SecurityBundle\Entity\User;
use OHMedia\SecurityBundle\Repository\UserRepository;
use OHMedia\TimezoneBundle\Util\DateTimeUtil;
use OHMedia\UtilityBundle\Util\RandomString;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[Admin]
class PasswordController extends AbstractController
{
    #[Route('/forgot-password', name: 'user_forgot_password')]
    public function forgotPassword(
        EmailRepository $emailRepository,
        Request $request,
        UserRepository $userRepository
    ): Response {
        if ($loggedIn = $this->getUser()) {
            $this->addFlash('warning', 'You are already logged in and can change your password below.');

            return $this->redirectToRoute('user_edit', [
                'id' => $loggedIn->getId(),
            ]);
        }

        $formBuilder = $this->createFormBuilder(null, [
            'honeypot_protection' => true,
        ]);

        $form = $formBuilder
            ->add('email', EmailType::class)
            ->add('captcha', CaptchaType::class)
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

            if ($currentReset && DateTimeUtil::isFuture($currentReset)) {
                $this->addFlash('warning', 'You recently requested a password reset.');

                return $this->redirectToRoute('user_forgot_password');
            }

            $token = RandomString::get(50, function ($token) use ($userRepository) {
                return !$userRepository->findOneBy([
                    'reset_token' => $token,
                ]);
            });

            $nextReset = new \DateTimeImmutable('+1 hour');
            $resetExpires = new \DateTimeImmutable('+2 hours');

            $user
                ->setResetToken($token)
                ->setNextReset($nextReset)
                ->setResetExpires($resetExpires)
            ;

            $userRepository->save($user, true);

            $this->createPasswordResetEmail($emailRepository, $user);

            $this->addFlash('notice', 'Check your email for a link to reset your password.');

            return $this->redirectToRoute('user_forgot_password');
        }

        return $this->render('@OHMediaSecurity/form/forgot_password_form.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    private function createPasswordResetEmail(EmailRepository $emailRepository, User $user)
    {
        $url = $this->generateUrl('user_password_reset', [
            'token' => $user->getResetToken(),
        ], UrlGeneratorInterface::ABSOLUTE_URL);

        $to = new EmailAddress($user->getEmail(), $user->getFullName());

        $email = (new Email())
            ->setSubject('Password Reset')
            ->setTemplate('@OHMediaSecurity/email/password_reset_email.html.twig', [
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
    ): Response {
        $user = $token
            ? $userRepository->findOneBy([
                'reset_token' => $token,
            ])
            : null;

        if (!$user) {
            $this->addFlash('error', 'Invalid password reset token.');

            return $this->redirectToRoute('user_forgot_password');
        }

        $resetExpires = $user->getResetExpires();

        if (!$resetExpires || DateTimeUtil::isPast($resetExpires)) {
            $this->addFlash('error', 'This password reset is expired. Please start a new one.');

            return $this->redirectToRoute('user_forgot_password');
        }

        $form = $this->createFormBuilder()
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'options' => ['attr' => ['autocomplete' => 'new-password']],
                'invalid_message' => 'The password fields must match.',
                'first_options' => ['label' => 'New Password'],
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

        return $this->render('@OHMediaSecurity/form/password_reset_form.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
