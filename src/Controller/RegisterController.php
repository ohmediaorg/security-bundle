<?php

namespace OHMedia\SecurityBundle\Controller;

use OHMedia\EmailBundle\Entity\Email;
use OHMedia\EmailBundle\Repository\EmailRepository;
use OHMedia\EmailBundle\Util\EmailAddress;
use OHMedia\SecurityBundle\Entity\User;
use OHMedia\SecurityBundle\Form\RegisterType;
use OHMedia\SecurityBundle\Repository\UserRepository;
use OHMedia\UtilityBundle\Util\RandomString;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class RegisterController extends AbstractController
{
    #[Route('/register', name: 'user_register')]
    public function forgotPassword(
        EmailRepository $emailRepository,
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        UserRepository $userRepository
    ): Response
    {
        // TODO: config to enable/disable registration

        if ($this->getUser()) {
            $this->addFlash('warning', 'You are already logged in.');

            return $this->redirectToRoute('user_index');
        }

        $user = new User();

        $form = $this->createForm(RegisterType::class, $user, [
            'honeypot_protection' => true,
        ]);

        $form->add('submit', SubmitType::class);

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
                ->setEnabled(true)
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
}
