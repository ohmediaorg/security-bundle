<?php

namespace OHMedia\SecurityBundle\Service;

use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use OHMedia\EmailBundle\Entity\Email;
use OHMedia\EmailBundle\Repository\EmailRepository;
use OHMedia\EmailBundle\Util\EmailAddress;
use OHMedia\SecurityBundle\Entity\User;
use OHMedia\SecurityBundle\Repository\UserRepository;
use OHMedia\UtilityBundle\Util\RandomString;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class UserLifecycle
{
    public function __construct(
        private EmailRepository $emailRepository,
        private UrlGeneratorInterface $urlGenerator,
        private UserRepository $userRepository,
    ) {
    }

    public function preUpdate(User $user, PreUpdateEventArgs $event)
    {
        if (!$user->wasEmailJustVerified() && $event->hasChangedField('email')) {
            $oldEmail = $event->getOldValue('email');
            $newEmail = $event->getNewValue('email');

            $emailChanged = $oldEmail !== $newEmail;
        } else {
            $emailChanged = false;
        }

        if ($emailChanged) {
            $verifyToken = RandomString::get(50, function ($token) {
                return !$this->userRepository->findOneBy([
                    'verify_token' => $token,
                ]);
            });

            $user->doEmailVerification($oldEmail, $newEmail, $verifyToken);
        }
    }

    public function postUpdate(User $user, PostUpdateEventArgs $event)
    {
        if ($user->shouldSendVerifyEmail()) {
            $url = $this->urlGenerator->generate('user_verify_email', [
                'token' => $user->getVerifyToken(),
            ], UrlGeneratorInterface::ABSOLUTE_URL);

            $to = new EmailAddress($user->getVerifyEmail(), $user->getFullName());

            $email = (new Email())
                ->setSubject('Verify Email Address')
                ->setTemplate('@OHMediaSecurity/email/verification_email.html.twig', [
                    'user' => $user,
                    'url' => $url,
                ])
                ->setTo($to)
            ;

            $this->emailRepository->save($email, true);
        }
    }
}
