<?php

namespace App\Controller;

use App\Form\__PASCALCASE__Type;
use App\Provider\__PASCALCASE__Provider;
use OHMedia\SecurityBundle\Controller\EntityController;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class __PASCALCASE__Controller extends EntityController
{
    public function __construct(__PASCALCASE__Provider $provider)
    {
        $this->setProvider($provider);
    }

    #[Route('/__KEBABCASE__/create', name: '__SNAKECASE___create')]
    public function createAction(Request $request)
    {
        return parent::createAction($request);
    }

    #[Route(
        '/__KEBABCASE__/{id}/{action}',
        name: '__SNAKECASE___action',
        defaults: ['action' => 'read'],
        requirements: ['id' => '\d+']
    )]
    public function actionAction(Request $request, $action)
    {
        return parent::actionAction($request, $action);
    }

    protected function getActionRoute()
    {
        return '__SNAKECASE___action';
    }

    protected function getEntityFormClass()
    {
        return __PASCALCASE__Type::class;
    }

    protected function redirectDeleteAction()
    {
        // redirect to list page
    }

    protected function redirectCancelAction()
    {
        if ($this->entity->getId()) {
            return $this->redirectToAction('read');
        }
        else {
            // redirect to list page
        }
    }

    protected function redirectSaveAction()
    {
        return $this->redirectToAction('read');
    }

    protected function renderSaveAction(FormView $formView)
    {
        return $this->render('__CAMELCASE__/form.html.twig', [
            'form' => $formView
        ]);
    }

    protected function renderDeleteAction(FormView $formView)
    {
        return $this->render('__CAMELCASE__/delete.html.twig', [
            'form' => $formView
        ]);
    }
}
