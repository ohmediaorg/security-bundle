<?php

namespace OHMedia\SecurityBundle\Controller;

use OHMedia\EmailBundle\Entity\Email;
use OHMedia\EmailBundle\Repository\EmailRepository;
use OHMedia\EmailBundle\Util\EmailAddress;
use OHMedia\SecurityBundle\Entity\User;
use OHMedia\SecurityBundle\Form\DeleteType;
use OHMedia\SecurityBundle\Form\UserType;
use OHMedia\SecurityBundle\Repository\UserRepository;
use OHMedia\SecurityBundle\Security\Voter\UserVoter;
use OHMedia\UtilityBundle\Util\RandomString;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

abstract class UserBackendController extends AbstractController
{
    abstract protected function indexRender(UserRepository $userRepository): Response;
    abstract protected function createRender(FormView $formView, User $user): Response;
    abstract protected function editRender(FormView $formView, User $user): Response;
    abstract protected function deleteRender(FormView $formView, User $user): Response;

    #[Route('/users', name: 'user_index', methods: ['GET'])]
    public function index(UserRepository $userRepository): Response
    {
        $this->denyAccessUnlessGranted(
            UserVoter::INDEX,
            new User(),
            'You cannot access the list of users.'
        );

        return $this->indexRender($userRepository);
    }

    #[Route('/user/create', name: 'user_create', methods: ['GET', 'POST'])]
    public function create(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        UserRepository $userRepository
    ): Response {
        $user = new User();

        $this->denyAccessUnlessGranted(
            UserVoter::CREATE,
            $user,
            'You cannot create a new user.'
        );

        $form = $this->createForm(UserType::class, $user, [
            'logged_in' => $this->getUser(),
        ]);

        $form->add('submit', SubmitType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $hashedPassword = $passwordHasher->hashPassword(
                $user,
                $form->get('password')->getData()
            );

            $user->setPassword($hashedPassword);

            $userRepository->save($user, true);

            $this->addFlash('notice', 'Changes to the user were saved successfully.');

            return $this->createRedirect($user);
        }

        return $this->createRender($form->createView(), $user);
    }

    protected function createRedirect(User $user): Response
    {
        return $this->redirectToRoute('user_index');
    }

    #[Route('/user/{id}/edit', name: 'user_edit', methods: ['GET', 'POST'])]
    public function edit(
        EmailRepository $emailRepository,
        Request $request,
        User $user,
        UserPasswordHasherInterface $passwordHasher,
        UserRepository $userRepository
    ): Response {
        $this->denyAccessUnlessGranted(
            UserVoter::EDIT,
            $user,
            'You cannot edit this user.'
        );

        $form = $this->createForm(UserType::class, $user, [
            'logged_in' => $this->getUser(),
        ]);

        $form->add('submit', SubmitType::class);

        $oldEmail = $user->getEmail();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $newEmail = $form->get('email')->getData();

            $verifyEmail = $oldEmail !== $newEmail;

            if ($verifyEmail) {
                $token = RandomString::get(50, function ($token) use ($userRepository) {
                    return !$userRepository->findOneBy([
                        'verify_token' => $token,
                    ]);
                });

                $user
                    ->setEmail($oldEmail)
                    ->setVerifyToken($token)
                    ->setVerifyEmail($newEmail)
                ;
            }

            $password = $form->get('password')->getData();

            if ($password) {
                $hashedPassword = $passwordHasher->hashPassword(
                    $user,
                    $password
                );

                $user->setPassword($hashedPassword);
            }

            $userRepository->save($user, true);

            if ($verifyEmail) {
                $this->addFlash('notice', 'Changes to the user were saved successfully. The new email address will need to be verified before that change takes effect.');

                $this->createVerificationEmail($emailRepository, $user);
            } else {
                $this->addFlash('notice', 'Changes to the user were saved successfully.');
            }

            return $this->editRedirect($user);
        }

        return $this->editRender($form->createView(), $user);
    }

    protected function editRedirect(User $user): Response
    {
        return $this->redirectToRoute('user_index');
    }

    private function createVerificationEmail(EmailRepository $emailRepository, User $user)
    {
        $url = $this->generateUrl('user_verify_email', [
            'token' => $user->getVerifyToken(),
        ], UrlGeneratorInterface::ABSOLUTE_URL);

        $to = new EmailAddress($user->getVerifyEmail(), $user->getFullName());

        $email = (new Email())
            ->setSubject('Verify Email Address')
            ->setTemplate('@OHMediaSecurity/verification_email.html.twig', [
                'user' => $user,
                'url' => $url,
            ])
            ->setTo($to)
        ;

        $emailRepository->save($email, true);
    }

    #[Route('/user/{id}/delete', name: 'user_delete', methods: ['GET', 'POST'])]
    public function delete(
        Request $request,
        User $user,
        UserRepository $userRepository
    ): Response {
        $this->denyAccessUnlessGranted(
            UserVoter::DELETE,
            $user,
            'You cannot delete this user.'
        );

        $form = $this->createForm(DeleteType::class, null);

        $form->add('delete', SubmitType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $userRepository->remove($user, true);

            $this->addFlash('notice', 'The user was deleted successfully.');

            return $this->deleteRedirect($user);
        }

        return $this->deleteRender($form->createView(), $user);
    }

    protected function deleteRedirect(User $user): Response
    {
        return $this->redirectToRoute('user_index');
    }
}
