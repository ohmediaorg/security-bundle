<?php

namespace OHMedia\SecurityBundle\Controller;

use OHMedia\BackendBundle\Routing\Attribute\Admin;
use OHMedia\SecurityBundle\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Admin]
class VerificationController extends AbstractController
{
    #[Route('/verify-email/{token}', name: 'user_verify_email')]
    public function verifyEmail(
        UserRepository $userRepository,
        string $token
    ): Response {
        $user = $token
            ? $userRepository->findOneBy([
                'verify_token' => $token,
            ])
            : null;

        $loggedIn = $this->getUser();

        if (!$user) {
            $this->addFlash('error', 'Invalid verification token.');

            return $this->redirectToRoute('user_profile');
        }

        $user
            ->setEmail($user->getVerifyEmail())
            ->setVerifyToken(null)
            ->setVerifyEmail(null)
        ;

        $userRepository->save($user, true);

        $this->addFlash('notice', 'Your email is verified.');

        return $this->redirectToRoute('user_profile');
    }
}
