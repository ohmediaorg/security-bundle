<?php

namespace OHMedia\SecurityBundle\Controller;

use OHMedia\BackendBundle\Routing\Attribute\Admin;
use OHMedia\SecurityBundle\Form\ProfileType;
use OHMedia\SecurityBundle\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Admin]
class ProfileController extends AbstractController
{
    #[Route('/profile', name: 'user_profile', methods: ['GET', 'POST'])]
    public function __invoke(
        Request $request,
        UserRepository $userRepository,
    ): Response {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $user = $this->getUser();

        $form = $this->createForm(ProfileType::class, $user);

        $form->add('save', SubmitType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $userRepository->save($user, true);

                if ($user->shouldSendVerifyEmail()) {
                    $this->addFlash('notice', 'Changes to your profile were saved successfully. The new email address will need to be verified before that change takes effect.');
                } else {
                    $this->addFlash('notice', 'Changes to your profile were saved successfully.');
                }

                return $this->redirectToRoute('user_profile');
            }

            $this->addFlash('error', 'There are some errors in the form below.');
        }

        return $this->render('@OHMediaSecurity/user/user_profile.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
            'form_title' => 'Profile',
        ]);
    }
}
