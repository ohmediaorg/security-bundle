<?php

namespace OHMedia\SecurityBundle\Controller;

use OHMedia\BackendBundle\Routing\Attribute\Admin;
use OHMedia\BootstrapBundle\Service\Paginator;
use OHMedia\EmailBundle\Entity\Email;
use OHMedia\EmailBundle\Repository\EmailRepository;
use OHMedia\EmailBundle\Util\EmailAddress;
use OHMedia\SecurityBundle\Entity\User;
use OHMedia\SecurityBundle\Form\UserType;
use OHMedia\SecurityBundle\Repository\UserRepository;
use OHMedia\SecurityBundle\Security\Voter\UserVoter;
use OHMedia\UtilityBundle\Form\DeleteType;
use OHMedia\UtilityBundle\Util\RandomString;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[Admin]
class UserController extends AbstractController
{
    public function __construct(private UserRepository $userRepository)
    {
    }

    #[Route('/users', name: 'user_index', methods: ['GET'])]
    public function index(Paginator $paginator): Response
    {
        $this->denyAccessUnlessGranted(
            UserVoter::INDEX,
            new User(),
            'You cannot access the list of users.'
        );

        $qb = $this->userRepository->createQueryBuilder('u');

        $loggedIn = $this->getUser();

        if (!$loggedIn->isTypeDeveloper()) {
            $qb->where('(u.type <> :developer)')
                ->setParameter('developer', User::TYPE_DEVELOPER);
        }

        return $this->render('@OHMediaSecurity/user/user_index.html.twig', [
            'pagination' => $paginator->paginate($qb, 20),
            'new_user' => new User(),
            'attributes' => [
                'create' => UserVoter::CREATE,
                'delete' => UserVoter::DELETE,
                'edit' => UserVoter::EDIT,
            ],
        ]);
    }

    #[Route('/user/create', name: 'user_create', methods: ['GET', 'POST'])]
    public function create(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
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

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $hashedPassword = $passwordHasher->hashPassword(
                    $user,
                    $form->get('password')->getData()
                );

                $user->setPassword($hashedPassword);

                $this->userRepository->save($user, true);

                $this->addFlash('notice', 'Changes to the user were saved successfully.');

                return $this->redirectToRoute('user_index');
            }

            $this->addFlash('error', 'There are some errors in the form below.');
        }

        return $this->render('@OHMediaSecurity/user/user_form.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
            'form_title' => 'Create User',
            'is_profile' => false,
        ]);
    }

    #[Route('/profile', name: 'user_profile', methods: ['GET', 'POST'])]
    public function profile(
        EmailRepository $emailRepository,
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
    ): Response {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        return $this->editForm(
            $emailRepository,
            $request,
            $this->getUser(),
            $passwordHasher,
            true
        );
    }

    #[Route('/user/{id}/edit', name: 'user_edit', methods: ['GET', 'POST'])]
    public function edit(
        EmailRepository $emailRepository,
        Request $request,
        User $user,
        UserPasswordHasherInterface $passwordHasher,
    ): Response {
        $this->denyAccessUnlessGranted(
            UserVoter::EDIT,
            $user,
            'You cannot edit this user.'
        );

        return $this->editForm(
            $emailRepository,
            $request,
            $user,
            $passwordHasher,
            false
        );
    }

    private function editForm(
        EmailRepository $emailRepository,
        Request $request,
        User $user,
        UserPasswordHasherInterface $passwordHasher,
        bool $isProfile
    ): Response {
        $form = $this->createForm(UserType::class, $user, [
            'logged_in' => $this->getUser(),
        ]);

        $form->add('submit', SubmitType::class);

        $oldEmail = $user->getEmail();

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $newEmail = $form->get('email')->getData();

                $verifyEmail = $oldEmail !== $newEmail;

                if ($verifyEmail) {
                    $token = RandomString::get(50, function ($token) {
                        return !$this->userRepository->findOneBy([
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

                $this->userRepository->save($user, true);

                $noun = $isProfile ? 'your profile' : 'the user';

                if ($verifyEmail) {
                    $this->addFlash('notice', "Changes to $noun were saved successfully. The new email address will need to be verified before that change takes effect.");

                    $this->createVerificationEmail($emailRepository, $user);
                } else {
                    $this->addFlash('notice', "Changes to $noun were saved successfully.");
                }

                return $isProfile
                    ? $this->redirectToRoute('user_profile')
                    : $this->redirectToRoute('user_index');
            }

            $this->addFlash('error', 'There are some errors in the form below.');
        }

        return $this->render('@OHMediaSecurity/user/user_form.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
            'form_title' => $isProfile ? 'Profile' : 'Edit User',
            'is_profile' => $isProfile,
        ]);
    }

    private function createVerificationEmail(EmailRepository $emailRepository, User $user)
    {
        $url = $this->generateUrl('user_verify_email', [
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

        $emailRepository->save($email, true);
    }

    #[Route('/user/{id}/delete', name: 'user_delete', methods: ['GET', 'POST'])]
    public function delete(
        Request $request,
        User $user,
    ): Response {
        $this->denyAccessUnlessGranted(
            UserVoter::DELETE,
            $user,
            'You cannot delete this user.'
        );

        $form = $this->createForm(DeleteType::class, null);

        $form->add('delete', SubmitType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $this->userRepository->remove($user, true);

                $this->addFlash('notice', 'The user was deleted successfully.');

                return $this->redirectToRoute('user_index');
            }

            $this->addFlash('error', 'There are some errors in the form below.');
        }

        return $this->render('@OHMediaSecurity/user/user_delete.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
            'form_title' => sprintf('Delete User %s', $user->getEmail()),
        ]);
    }
}
