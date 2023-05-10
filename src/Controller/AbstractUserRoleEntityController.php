<?php

namespace App\Controller\Backend;

use OHMedia\SecurityBundle\Controller\EntityController;
use OHMedia\SecurityBundle\Form\UserRoleType;
use OHMedia\SecurityBundle\Provider\UserRoleProvider;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class AbstractUserRoleEntityController extends EntityController
{
    public function __construct(UserRoleProvider $provider)
    {
        $this->setProvider($provider);
    }

    #[Route('/user/role/create', name: 'user_role_create')]
    public function createAction(Request $request)
    {
        return parent::createAction($request);
    }

    #[Route(
        '/user/role/{id}/{action}',
        name: 'user_role_action',
        defaults: ['action' => 'read'],
        requirements: ['id' => '\d+']
    )]
    public function actionAction(Request $request, $action)
    {
        return parent::actionAction($request, $action);
    }

    protected function getActionRoute()
    {
        return 'user_role_action';
    }

    protected function getEntityFormClass()
    {
        return UserRoleType::class;
    }
}
