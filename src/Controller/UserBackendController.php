<?php

namespace OHMedia\SecurityBundle\Controller;

use OHMedia\SecurityBundle\Entity\User;
use OHMedia\SecurityBundle\Form\DeleteType;
use OHMedia\SecurityBundle\Form\UserType;
use OHMedia\SecurityBundle\Repository\UserRepository;
use OHMedia\SecurityBundle\Security\Voter\UserVoter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

abstract class UserBackendController extends AbstractController
{
    abstract protected function indexRender(UserRepository $userRepository): Response;
    abstract protected function formRender(FormView $formView, User $user): Response;
    abstract protected function deleteRender(FormView $formView, User $user): Response;

    #[Route('/users', name: 'user_index', methods: ['GET'])]
    public function index(UserRepository $userRepository): Response
    {
        $this->denyAccessUnlessGranted(
            UserVoter::INDEX,
            new User,
            'You cannot access the list of users.'
        );

        return $this->indexRender($userRepository);
    }

    #[Route('/user/create', name: 'user_create', methods: ['GET', 'POST'])]
    public function create(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        UserRepository $userRepository
    ): Response
    {
        $user = new User();

        $this->denyAccessUnlessGranted(
            UserVoter::CREATE,
            $user,
            'You cannot create a new user.'
        );

        return $this->form($request, $user, $passwordHasher, $userRepository);
    }

    #[Route('/user/{id}/edit', name: 'user_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        User $user,
        UserPasswordHasherInterface $passwordHasher,
        UserRepository $userRepository
    ): Response
    {
        $this->denyAccessUnlessGranted(
            UserVoter::EDIT,
            $user,
            'You cannot edit this user.'
        );

        return $this->form($request, $user, $passwordHasher, $userRepository);
    }

    private function form(
        Request $request,
        User $user,
        UserPasswordHasherInterface $passwordHasher,
        UserRepository $userRepository
    ): Response
    {
        $creating = !$user->getId();

        $form = $this->createForm(UserType::class, $user, [
            'logged_in' => $this->getUser(),
        ]);

        $form->add('submit', SubmitType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $password = $form->get('password')->getData();

            if ($password) {
                $hashedPassword = $passwordHasher->hashPassword(
                    $user,
                    $password
                );

                $user->setPassword($hashedPassword);
            }

            $userRepository->save($user, true);

            $this->addFlash('notice', 'Changes to the user were saved successfully.');

            return $this->formRedirect($user, $creating);
        }

        return $this->formRender($form->createView(), $user);
    }

    protected function formRedirect(User $user, bool $creating): Response
    {
        return $this->redirectToRoute('user_index');
    }

    #[Route('/user/{id}/delete', name: 'user_delete', methods: ['GET', 'POST'])]
    public function delete(
        Request $request,
        User $user,
        UserRepository $userRepository
    ): Response
    {
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