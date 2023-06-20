<?php

namespace OHMedia\SecurityBundle\Controller;

use OHMedia\UtilityBundle\Util\RandomString;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class PasswordResetController extends AbstractController
{
    #[Route('/password-reset', name: 'user_password_reset')]
    public function __invoke(Request $request, UserRepository $userRepository): Response
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

                return $this->redirectToRoute('user_password_reset');
            }

            $currentPasswordReset = $user->getNextPasswordReset();

            $now = new \DateTime();

            if ($currentPasswordReset && $currentPasswordReset > $now) {
                $this->addFlash('warning', 'You recently requested a password reset.');

                return $this->redirectToRoute('user_password_reset');
            }

            $token = RandomString::get(25, function($token) {
                return !$userRepository->findByToken($token);
            });

            $nextPasswordReset = new \DateTime('+1 hour');

            $user
                ->setPasswordResetToken($token)
                ->setNextPasswordReset($nextPasswordReset)
            ;
        }

        return $this->render('@OHMediaSecurity/password-reset.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
