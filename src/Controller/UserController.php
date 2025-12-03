<?php

namespace OHMedia\SecurityBundle\Controller;

use OHMedia\BackendBundle\Form\MultiSaveType;
use OHMedia\BackendBundle\Routing\Attribute\Admin;
use OHMedia\BootstrapBundle\Service\Paginator;
use OHMedia\SecurityBundle\Entity\User;
use OHMedia\SecurityBundle\Form\UserType;
use OHMedia\SecurityBundle\Repository\UserRepository;
use OHMedia\SecurityBundle\Security\Voter\UserVoter;
use OHMedia\UtilityBundle\Form\DeleteType;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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

        $types = [
            User::TYPE_SUPER,
            User::TYPE_ADMIN,
        ];

        if ($loggedIn->isTypeDeveloper()) {
            $types[] = User::TYPE_DEVELOPER;
        }

        $qb->where('u.type IN (:types)')
            ->setParameter('types', $types);

        $qb->addSelect('COALESCE(u.first_name, u.email) AS HIDDEN ord');

        $qb->orderBy('ord', 'ASC');

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
    public function create(Request $request): Response
    {
        $user = new User();
        $user->setType(User::TYPE_ADMIN);

        $this->denyAccessUnlessGranted(
            UserVoter::CREATE,
            $user,
            'You cannot create a new user.'
        );

        $form = $this->createForm(UserType::class, $user, [
            'logged_in' => $this->getUser(),
        ]);

        $form->add('save', MultiSaveType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $this->userRepository->save($user, true);

                $this->addFlash('notice', 'Changes to the user were saved successfully.');

                return $this->redirectForm($user, $form);
            }

            $this->addFlash('error', 'There are some errors in the form below.');
        }

        return $this->render('@OHMediaSecurity/user/user_form.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
            'form_title' => 'Create User',
        ]);
    }

    #[Route('/user/{id}/edit', name: 'user_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        #[MapEntity(id: 'id')] User $user
    ): Response {
        $this->denyAccessUnlessGranted(
            UserVoter::EDIT,
            $user,
            'You cannot edit this user.'
        );

        $form = $this->createForm(UserType::class, $user, [
            'logged_in' => $this->getUser(),
        ]);

        $form->add('save', MultiSaveType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $this->userRepository->save($user, true);

                if ($user->shouldSendVerifyEmail()) {
                    $this->addFlash('notice', 'Changes to the user were saved successfully. The new email address will need to be verified before that change takes effect.');
                } else {
                    $this->addFlash('notice', 'Changes to the user were saved successfully.');
                }

                return $this->redirectForm($user, $form);
            }

            $this->addFlash('error', 'There are some errors in the form below.');
        }

        return $this->render('@OHMediaSecurity/user/user_form.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
            'form_title' => 'Edit User',
        ]);
    }

    private function redirectForm(User $user, FormInterface $form): Response
    {
        $clickedButtonName = $form->getClickedButton()->getName() ?? null;

        if ('keep_editing' === $clickedButtonName) {
            return $this->redirectToRoute('user_edit', [
                'id' => $user->getId(),
            ]);
        } elseif ('add_another' === $clickedButtonName) {
            return $this->redirectToRoute('user_create');
        } else {
            return $this->redirectToRoute('user_index');
        }
    }

    #[Route('/user/{id}/delete', name: 'user_delete', methods: ['GET', 'POST'])]
    public function delete(
        Request $request,
        #[MapEntity(id: 'id')] User $user
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
