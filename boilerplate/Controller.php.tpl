<?php

namespace App\Controller;

use App\Form\__PASCALCASE__Type;
use App\Provider\__PASCALCASE__Provider;
use OHMedia\SecurityBundle\Controller\EntityController;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\Request;

class __PASCALCASE__Controller extends EntityController
{
    public function __construct(__PASCALCASE__Provider $provider)
    {
        $this->setProvider($provider);
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

    protected function redirectUnlockAction()
    {
        return $this->redirectToAction('update');
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
        // Symfony\Bundle\FrameworkBundle\Controller\Controller::render()
        // return $this->render(...);
    }

    protected function renderDeleteAction(FormView $formView)
    {
        // Symfony\Bundle\FrameworkBundle\Controller\Controller::render()
        // return $this->render(...);
    }
}
