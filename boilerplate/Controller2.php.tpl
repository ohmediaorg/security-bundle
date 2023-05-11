<?php

namespace App\Controller;

use App\Entity\__PASCALCASE__;
use App\Form\__PASCALCASE__Type;
use App\Repository\__PASCALCASE__Repository;
use App\Security\Voter\__PASCALCASE__Voter;
use OHMedia\SecurityBundle\Form\DeleteType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\LockInterface;
use Symfony\Component\Lock\Store\SemaphoreStore;
use Symfony\Component\Routing\Annotation\Route;

class __PASCALCASE__Controller extends AbstractController
{
    #[Route('/__KEBABCASE__', name: '__SNAKECASE___index', methods: ['GET'])]
    public function index(__PASCALCASE__Repository $__CAMELCASE__Repository): Response
    {
        $this->denyAccessUnlessGranted(
            __PASCALCASE__Voter::CREATE,
            $__CAMELCASE__,
            'You cannot create a new __READABLE__.'
        );

        $__CAMELCASE__s = $__CAMELCASE__Repository->findAll();

        return $this->render('__CAMELCASE__/index.html.twig', [
            '__CAMELCASE__s' => $__CAMELCASE__s,
        ]);
    }

    #[Route('/__KEBABCASE__/create', name: '__SNAKECASE___create', methods: ['GET', 'PUT'])]
    public function create(Request $request): Response
    {
        $__CAMELCASE__ = new __PASCALCASE__();

        $this->denyAccessUnlessGranted(
            __PASCALCASE__Voter::CREATE,
            $__CAMELCASE__,
            'You cannot create a new __READABLE__.'
        );

        return $this->form($request, $__CAMELCASE__);
    }

    #[Route('/__KEBABCASE__/{id}', name: '__SNAKECASE___view', methods: ['GET'])]
    public function view(__PASCALCASE__ $__CAMELCASE__): Response
    {
        $this->denyAccessUnlessGranted(
            __PASCALCASE__Voter::VIEW,
            $__CAMELCASE__,
            'You cannot view this __READABLE__.'
        );

        return $this->render('__CAMELCASE__/view.html.twig', [
            'form' => $form->createView(),
            '__CAMELCASE__' => $__CAMELCASE__,
        ]);
    }

    #[Route('/__KEBABCASE__/{id}/edit', name: '__SNAKECASE___edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, __PASCALCASE__ $__CAMELCASE__): Response
    {
        $this->denyAccessUnlessGranted(
            __PASCALCASE__Voter::EDIT,
            $__CAMELCASE__,
            'You cannot edit this __READABLE__.'
        );

        $store = new SemaphoreStore();
        $factory = new LockFactory($store);
        $lock = $factory->createLock(
            '__CAMELCASE__' . $__CAMELCASE__->getId(),
            300,
            false
        );

        if (!$lock->acquire()) {
            $this->addFlash('notice', 'Editing of this __READABLE__ is temporarily locked by another user.');

            return $this->redirectToRoute('__SNAKECASE___view', [
                'id' => $__CAMELCASE__->getId(),
            ]);
        }

        return $this->form($request, $__CAMELCASE__, $lock);
    }

    #[Route('/__KEBABCASE__/{id}/delete', name: '__SNAKECASE___delete', methods: ['GET', 'POST'])]
    public function delete(Request $request, __PASCALCASE__ $__CAMELCASE__): Response
    {
        $this->denyAccessUnlessGranted(
            __PASCALCASE__Voter::DELETE,
            $__CAMELCASE__,
            'You cannot delete this __READABLE__.'
        );

        $form = $this->createForm(DeleteType::class, null);

        $form->add('delete', SubmitType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($__CAMELCASE__);
            $em->flush();

            $this->addFlash('notice', 'The __READABLE__ was deleted successfully.');

            return $this->redirectToRoute('__SNAKECASE___index');
        }

        return $this->render('__CAMELCASE__/delete.html.twig', [
            'form' => $form->createView(),
            '__CAMELCASE__' => $__CAMELCASE__,
        ]);
    }

    private function form(Request $request, __PASCALCASE__ $__CAMELCASE__, ?LockInterface $lock = null): Response
    {
        $form = $this->createForm(__PASCALCASE__Type::class, $__CAMELCASE__);

        if (!$__CAMELCASE__->getId()) {
            $form->setMethod('PUT');
        }

        $form->add('submit', SubmitType::class);

        // TODO: add back cancel button
        // and do $lock->release() on cancel

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            if (!$__CAMELCASE__->getId()) {
                $em->persist($__CAMELCASE__);
            }

            $em->flush();

            if ($lock) {
                $lock->release();
            }

            $this->addFlash('notice', 'Changes to the __READABLE__ were saved successfully.');

            return $this->redirectToRoute('__SNAKECASE___view', [
                'id' => $__CAMELCASE__->getId(),
            ]);
        }

        return $this->render('__CAMELCASE__/form.html.twig', [
            'form' => $form->createView(),
            '__CAMELCASE__' => $__CAMELCASE__,
        ]);
    }
}
