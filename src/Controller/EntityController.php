<?php

namespace OHMedia\SecurityBundle\Controller;

use OHMedia\SecurityBundle\Form\DeleteType;
use OHMedia\SecurityBundle\Form\Type\ActionsType;
use OHMedia\SecurityBundle\Provider\AbstractEntityProvider;
use LogicException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\Request;

use function Symfony\Component\String\u;

abstract class EntityController extends AbstractController
{
    use Traits\LockingController;

    protected $ajax;
    protected $em;
    protected $entity;
    protected $provider;
    protected $request;
    protected $user;

    abstract protected function getActionRoute();
    abstract protected function getEntityFormClass();
    abstract protected function redirectDeleteAction();
    abstract protected function renderDeleteAction(FormView $formView);
    abstract protected function redirectCancelAction();
    abstract protected function redirectSaveAction();
    abstract protected function renderSaveAction(FormView $formView);

    public function createAction(Request $request)
    {
        $this->preActionSetup($request, 'create', false);

        return $this->saveAction();
    }

    public function readAction(Request $request)
    {
        $this->preActionSetup($request, 'read');

        return $this->renderViewAction();
    }

    public function updateAction(Request $request)
    {
        $this->preActionSetup($request, 'update');

        if ($locked = $this->doLocking()) {
            return $this->redirectSaveAction();
        }

        return $this->saveAction();
    }

    public function deleteAction(Request $request)
    {
        $this->preActionSetup($request, 'delete');

        $form = $this->createForm(DeleteType::class, null);

        $this->addDeleteFormActions($form);

        if ($this->isSubmitCancelled($form)) {
            $this->addFlashWarning($this->entityCancelDeleteWarningMessage());

            return $this->redirectCancelAction();
        }

        $form->handleRequest($this->request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->provider->delete($this->entity);

            $this->addFlashNotice($this->entityDeleteNoticeMessage());

            return $this->redirectDeleteAction();
        }

        return $this->renderDeleteAction($form->createView());
    }

    protected function addDeleteFormActions(FormInterface $form)
    {
        $form->add('actions', ActionsType::class, [
            'save_options' => [
                'label' => 'Delete',
                'attr' => [
                    'class' => 'btn-danger'
                ]
            ],
            'cancel_options' => [
                'attr' => [
                    'class' => 'btn-secondary'
                ]
            ]
        ]);
    }

    /**
     * Helper action to reduce route definitions.
     * The following could handle many actions (read, update, delete, etc.):
     *
     * eg. entity_action:
     *         path: /entity/{id}/{action}
     *         controller: App\Controller\MyEntityController::actionAction
     *         defaults: { action: read }
     *         requirements:
     *             id: \d+
     *
     * The calling controller (in this case AppBundle:Entity)
     * must have a callable method readAction(), updateAction(), deleteAction(), etc.
     */
    public function actionAction(Request $request, $action)
    {
        $controller = get_called_class();
        $method = u($action)->camel() . 'Action';
        $callable = "$controller::$method";

        if (!is_callable($callable)) {
            throw $this->createNotFoundException();
        }

        $params = $request->attributes->get('_route_params');
        unset($params['action']);

        return $this->forward($callable, $params, $request->query->all());
    }

    /**
     * Called after the form is built
     * and before the parameters are mapped to the entity
     *
     * @param FormInterface $form
     */
    protected function entityPostFormBuild(FormInterface $form) {}

    /**
     * Called after parameters are mapped to the entity
     * and before the form is validated
     *
     * @param FormInterface $form
     */
    protected function entityPreValidate(FormInterface $form) {}

    /**
     * Called before the entity is persisted
     *
     * @param FormInterface $form
     */
    protected function entityPreSave(FormInterface $form) {}

    /**
     * Called after the entity is persisted
     *
     * @param bool $created true if the entity was newly created
     */
    protected function entityPostSave(bool $created) {}

    /**
     * Common functionality for creating/updating an entity
     */
    protected function saveAction()
    {
        $creating = !$this->entity->getId();

        $form = $this->createForm($this->getEntityFormClass(), $this->entity);

        $this->entityPostFormBuild($form);

        $this->addSaveFormActions($form);

        if ($this->isSubmitCancelled($form)) {
            $this->addFlashWarning($this->entityCancelSaveWarningMessage());

            if (!$creating && $this->hasLockable()) {
                $this->lockEntityRaw();
            }

            return $this->redirectCancelAction();
        }

        $form->handleRequest($this->request);

        $this->entityPreValidate($form);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityPreSave($form);

            $this->unlockEntity();

            $this->provider->save($this->entity);

            $this->entityPostSave($creating);

            $this->addFlashNotice($this->entitySaveNoticeMessage());

            return $this->redirectSaveAction();
        }

        return $this->renderSaveAction($form->createView());
    }

    protected function addSaveFormActions(FormInterface $form)
    {
        $form->add('actions', ActionsType::class, [
            'cancel_options' => [
                'attr' => [
                    'class' => 'btn-danger'
                ]
            ]
        ]);
    }

    protected function isSubmitCancelled(FormInterface $form)
    {
        $inputs = $this->request->request->get($form->getName());

        return isset($inputs['actions']['cancel']);
    }

    protected function renderViewAction()
    {
        throw new LogicException(sprintf('Please override \%s() or %s()', __METHOD__, '::readAction'));
    }

    protected function setProvider(AbstractEntityProvider $provider)
    {
        $this->provider = $provider;

        return $this;
    }

    /**
     * Helper function for setting up an action on an entity
     *
     * @param Request $request
     * @param string $action
     * @param bool $existing
     */
    protected function preActionSetup(Request $request, $action, $existing = true)
    {
        $this->setVariables($request);

        $this->setEntity($existing);

        $this->checkEntityAccess($action);
    }

    protected function setVariables(Request $request)
    {
        $this->request = $request;

        $this->ajax = $request->isXmlHttpRequest();

        $this->user = $this->getUser();

        $this->setEntityManager();
    }

    /**
     * Make the entity available to the controller
     *
     * @param bool $existing
     */
    protected function setEntity($existing = true)
    {
        $this->entity = $existing
            ? $this->getEntityExisting()
            : $this->getEntityNew();
    }

    protected function getEntityNew()
    {
        return $this->provider->create();
    }

    /**
     * Helper function for making the entity manager quickly available to the controller
     */
    protected function setEntityManager()
    {
        $this->em = $this->getDoctrine()->getManager();
    }

    /**
     * Leverages voters to determine if the action can be taken
     *
     * @param string $action
     */
    protected function checkEntityAccess($action)
    {
        $this->denyAccessUnlessGranted(
            $action,
            $this->entity,
            $this->entityAccessDeniedMessage($action)
        );
    }

    /**
     * Get the entity by $this->entity_request_param
     */
    protected function getEntityExisting()
    {
        $id = $this->request->get('id');

        $entity = $this->provider->get($id);

        if (!$entity) {
            throw $this->createNotFoundException($this->entityNotFoundMessage());
        }

        return $entity;
    }

    protected function redirectUnlockAction()
    {
        return $this->redirectToAction('update');
    }

    /**
     * Helper for redirecting to an action url on the entity
     *
     * @param string $action
     */
    protected function redirectToAction($action)
    {
        return $this->redirect($this->generateActionUrl($action));
    }

    /**
     * Helper for generating an action url on the entity
     *
     * @param string $action
     */
    protected function generateActionUrl($action)
    {
        return $this->generateUrl($this->getActionRoute(), [
            'id' => $this->entity->getId(),
            'action' => $action
        ]);
    }

    /**
     * Get the warning message to display in deleteAction() after cancelling
     */
    protected function entityCancelDeleteWarningMessage()
    {
        return sprintf('The %s was not deleted.', $this->provider->getHumanReadable());
    }

    /**
     * Get the notice message to display in deleteAction()
     */
    protected function entityDeleteNoticeMessage()
    {
        return sprintf('The %s was deleted successfully!', $this->provider->getHumanReadable());
    }

    /**
     * Get the warning message to display in saveAction() after cancelling
     */
    protected function entityCancelSaveWarningMessage()
    {
        return sprintf('The %s changes were not saved.', $this->provider->getHumanReadable());
    }

    /**
     * Get the notice message to display in saveAction() after saving
     */
    protected function entitySaveNoticeMessage()
    {
        return sprintf('The %s was saved successfully!', $this->provider->getHumanReadable());
    }

    /**
     * Get the error message to display if the entity is not found in getEntityExisting()
     */
    protected function entityNotFoundMessage()
    {
        return sprintf('The %s does not exist!', $this->provider->getHumanReadable());
    }

    /**
     * Get the error message to display if the action cannot be performed on the entity
     *
     * @param string $action
     */
    protected function entityAccessDeniedMessage($action)
    {
        return sprintf('Sorry, you cannot perform "%s" to this %s.', $action, $this->provider->getHumanReadable());
    }

    /**
     * Helper for adding a success/notice flash message
     *
     * @param string $message
     */
    protected function addFlashNotice($message)
    {
        $this->addFlash('notice', $message);
    }

    /**
     * Helper for adding a warning flash message
     *
     * @param string $message
     */
    protected function addFlashWarning($message)
    {
        $this->addFlash('warning', $message);
    }

    /**
     * Helper for adding an error flash message
     *
     * @param string $message
     */
    protected function addFlashError($message)
    {
        $this->addFlash('error', $message);
    }
}
