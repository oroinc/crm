<?php

namespace OroCRM\Bundle\TaskBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;

use Oro\Bundle\UserBundle\Entity\User;
use OroCRM\Bundle\TaskBundle\Entity\Task;
use OroCRM\Bundle\TaskBundle\Form\Type\TaskType;
use OroCRM\Bundle\TaskBundle\Entity\Repository\TaskRepository;

/**
 * @Route("/task")
 */
class TaskController extends Controller
{
    /**
     * @Route(
     *      ".{_format}",
     *      name="orocrm_task_index",
     *      requirements={"_format"="html|json"},
     *      defaults={"_format" = "html"}
     * )
     * @Acl(
     *      id="orocrm_task_view",
     *      type="entity",
     *      class="OroCRMTaskBundle:Task",
     *      permission="VIEW"
     * )
     * @Template
     */
    public function indexAction()
    {
        return [
            'entity_class' => $this->container->getParameter('orocrm_task.entity.class')
        ];
    }

    /**
     * @Route("/widget/sidebar-tasks/{perPage}", name="orocrm_task_widget_sidebar_tasks", defaults={"perPage" = 10})
     * @AclAncestor("orocrm_task_view")
     * @Template("OroCRMTaskBundle:Task/widget:tasksWidget.html.twig")
     */
    public function tasksWidgetAction($perPage)
    {
        /** @var TaskRepository $repository */
        $repository = $this->getRepository('OroCRM\Bundle\TaskBundle\Entity\Task');
        $id = $this->getUser()->getId();
        $perPage = (int)$perPage;
        $tasks = $repository->getTasksAssignedTo($id, $perPage);

        return array('tasks' => $tasks);
    }

    /**
     * @Route("/create", name="orocrm_task_create")
     * @Acl(
     *      id="orocrm_task_create",
     *      type="entity",
     *      class="OroCRMTaskBundle:Task",
     *      permission="CREATE"
     * )
     * @Template("OroCRMTaskBundle:Task:update.html.twig")
     */
    public function createAction()
    {
        $task = new Task();

        $defaultPriority = $this->getRepository('OroCRMTaskBundle:TaskPriority')->find('normal');
        if ($defaultPriority) {
            $task->setTaskPriority($defaultPriority);
        }

        $formAction = $this->get('oro_entity.routing_helper')
            ->generateUrlByRequest('orocrm_task_create', $this->getRequest());

        return $this->update($task, $formAction);
    }

    /**
     * @return User
     */
    protected function getCurrentUser()
    {
        $token = $this->container->get('security.context')->getToken();

        return $token ? $token->getUser() : null;
    }

    /**
     * @Route("/view/{id}", name="orocrm_task_view", requirements={"id"="\d+"})
     * @AclAncestor("orocrm_task_view")
     * @Template
     */
    public function viewAction(Task $task)
    {
        return array('entity' => $task);
    }

    /**
     * This action is used to render the list of tasks associated with the given entity
     * on the view page of this entity
     *
     * @Route(
     *      "/activity/view/{entityClass}/{entityId}",
     *      name="orocrm_task_activity_view"
     * )
     *
     * @AclAncestor("orocrm_task_view")
     * @Template
     */
    public function activityAction($entityClass, $entityId)
    {
        return array(
            'entity' => $this->get('oro_entity.routing_helper')->getEntity($entityClass, $entityId)
        );
    }

    /**
     * @Route("/update/{id}", name="orocrm_task_update", requirements={"id"="\d+"})
     * @Template
     * @Acl(
     *      id="orocrm_task_update",
     *      type="entity",
     *      class="OroCRMTaskBundle:Task",
     *      permission="EDIT"
     * )
     */
    public function updateAction(Task $task)
    {
        $formAction = $this->get('router')->generate('orocrm_task_update', ['id' => $task->getId()]);

        return $this->update($task, $formAction);
    }

    /**
     * @Route("/widget/info/{id}", name="orocrm_task_widget_info", requirements={"id"="\d+"})
     * @Template
     * @AclAncestor("orocrm_task_view")
     */
    public function infoAction(Task $entity)
    {
        return [
            'entity'         => $entity,
            'target'         => $this->getTargetEntity(),
            'renderContexts' => true
        ];
    }

    /**
     * @Route("/user/{userId}", name="orocrm_task_user_tasks", requirements={"userId"="\d+"})
     * @AclAncestor("orocrm_task_view")
     * @Template
     */
    public function userTasksAction($userId)
    {
        return ['userId' => $userId];
    }

    /**
     * @Route("/my", name="orocrm_task_my_tasks")
     * @AclAncestor("orocrm_task_view")
     * @Template
     */
    public function myTasksAction()
    {
        return [];
    }

    /**
     * @param Task $task
     * @param string $formAction
     * @return array
     */
    protected function update(Task $task, $formAction)
    {
        $saved = false;
        if ($this->get('orocrm_task.form.handler.task')->process($task)) {
            if (!$this->getRequest()->get('_widgetContainer')) {
                $this->get('session')->getFlashBag()->add(
                    'success',
                    $this->get('translator')->trans('orocrm.task.saved_message')
                );

                return $this->get('oro_ui.router')->redirectAfterSave(
                    array(
                        'route' => 'orocrm_task_update',
                        'parameters' => array('id' => $task->getId()),
                    ),
                    array(
                        'route' => 'orocrm_task_view',
                        'parameters' => array('id' => $task->getId()),
                    )
                );
            }
            $saved = true;
        }

        return array(
            'entity'     => $task,
            'saved'      => $saved,
            'form'       => $this->get('orocrm_task.form.handler.task')->getForm()->createView(),
            'formAction' => $formAction,
        );
    }

    /**
     * @return TaskType
     */
    protected function getFormType()
    {
        return $this->get('orocrm_task.form.handler.task')->getForm();
    }

    /**
     * @param string $entityName
     * @return \Doctrine\Common\Persistence\ObjectRepository
     */
    protected function getRepository($entityName)
    {
        return $this->getDoctrine()->getRepository($entityName);
    }

    /**
     * Get target entity
     *
     * @return object|null
     */
    protected function getTargetEntity()
    {
        $entityRoutingHelper = $this->get('oro_entity.routing_helper');
        $targetEntityClass   = $entityRoutingHelper->getEntityClassName($this->getRequest(), 'targetActivityClass');
        $targetEntityId      = $entityRoutingHelper->getEntityId($this->getRequest(), 'targetActivityId');
        if (!$targetEntityClass || !$targetEntityId) {
            return null;
        }

        return $entityRoutingHelper->getEntity($targetEntityClass, $targetEntityId);
    }
}
