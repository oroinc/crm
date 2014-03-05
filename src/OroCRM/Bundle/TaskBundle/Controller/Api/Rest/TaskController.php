<?php

namespace OroCRM\Bundle\TaskBundle\Controller\Api\Rest;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Response;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use FOS\Rest\Util\Codes;
use FOS\RestBundle\Controller\Annotations\NamePrefix;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Routing\ClassResourceInterface;

use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Oro\Bundle\UserBundle\Entity\User;
use OroCRM\Bundle\TaskBundle\Entity\Task;
use OroCRM\Bundle\TaskBundle\Entity\Repository\TaskRepository;
use OroCRM\Bundle\TaskBundle\Form\Type\TaskApiType;

/**
 * @RouteResource("task")
 * @NamePrefix("orocrm_api_")
 */
class TaskController extends FOSRestController implements ClassResourceInterface
{
    /**
     * Get the list of tasks
     *
     * @QueryParam(
     *      name="page",
     *      requirements="\d+",
     *      nullable=true,
     *      description="Page number, starting from 1. Defaults to 1."
     * )
     * @QueryParam(
     *      name="limit",
     *      requirements="\d+",
     *      nullable=true,
     *      description="Number of items per page. defaults to 10."
     * )
     * @ApiDoc(
     *      description="Get the list of tasks",
     *      resource=true,
     *      filters={
     *          {"name"="page", "dataType"="integer"},
     *          {"name"="limit", "dataType"="integer"}
     *      }
     * )
     * @AclAncestor("orocrm_task_view")
     * @return Response
     */
    public function cgetAction()
    {
        $page = (int) $this->getRequest()->get('page', 1);
        $limit = (int) $this->getRequest()->get('limit', 20);

        $qb = $this->getTaskRepository()
            ->createQueryBuilder('task')
            ->orderBy('task.createdAt', 'DESC')
            ->setFirstResult($page > 0 ? $page - 1 : 0)
            ->setMaxResults($limit);

        $query = $this->getOrmAclHelper()->apply($qb);
        $items = $query->execute();

        return $this->handleView(
            $this->view($items, is_array($items) ? Codes::HTTP_OK : Codes::HTTP_NOT_FOUND)
        );
    }

    /**
     * Get the list of tasks assigned to user
     *
     * @QueryParam(
     *      name="perPage",
     *      requirements="\d+",
     *      nullable=true,
     *      description="Number of items per page."
     * )
     * @ApiDoc(
     *      description="Get the list of assigned tasks",
     *      resource=true,
     *      filters={
     *          {"name"="perPage", "dataType"="integer"}
     *      }
     * )
     * @AclAncestor("orocrm_task_view")
     */
    public function getAssignedTasksAction()
    {
        $currentUser = $this->getCurrentUser();

        if (!$currentUser) {
            $this->handleView($this->view(null, Codes::HTTP_FORBIDDEN));
        }

        $perPage = (int) $this->getRequest()->get('perPage', 5);
        $perPage = $perPage > 0 ? $perPage : 5;

        $qb = $this->getTaskRepository()
            ->createQueryBuilder('task')
            ->orderBy('task.createdAt', 'DESC')
            ->where('task.assignee = :assignee')
            ->setParameter('assignee', $currentUser)
            ->setFirstResult(0)
            ->setMaxResults($perPage);

        $query = $this->getOrmAclHelper()->apply($qb);
        $items = $query->execute();

        return $this->handleView(
            $this->view($items, is_array($items) ? Codes::HTTP_OK : Codes::HTTP_NOT_FOUND)
        );
    }

    /**
     * Get task data
     *
     * @ApiDoc(
     *      description="Get task data",
     *      resource=true,
     *      requirements={
     *          {"name"="id", "dataType"="integer"},
     *      }
     * )
     * @AclAncestor("orocrm_task_view")
     */
    public function getAction(Task $id)
    {
        $task = $id;
        return $this->handleView($this->view($task, Codes::HTTP_OK));
    }

    /**
     * Create new task
     *
     * @ApiDoc(
     *      description="Create new task",
     *      resource=true
     * )
     * @AclAncestor("orocrm_task_create")
     */
    public function postAction()
    {
        $task = new Task();
        $form = $this->submitTask($task);

        if ($form->isValid()) {
            $view = $this->view($task, Codes::HTTP_CREATED);
        } else {
            $view = $this->view($form, Codes::HTTP_BAD_REQUEST);
        }

        return $this->handleView($view);
    }

    /**
     * Update existing task
     *
     * @ApiDoc(
     *      description="Update existing task",
     *      resource=true,
     *      requirements={
     *          {"name"="id", "dataType"="integer"},
     *      }
     * )
     * @AclAncestor("orocrm_task_update")
     */
    public function putAction(Task $id)
    {
        $task = $id;
        $form = $this->submitTask($task);

        if ($form->isValid()) {
            $view = $this->view($task, Codes::HTTP_OK);
        } else {
            $view = $this->view($form, Codes::HTTP_BAD_REQUEST);
        }

        return $this->handleView($view);
    }

    /**
     * Delete task
     *
     * @ApiDoc(
     *      description="Delete task",
     *      resource=true,
     *      requirements={
     *          {"name"="id", "dataType"="integer"},
     *      }
     * )
     * @Acl(
     *      id="orocrm_task_delete",
     *      type="entity",
     *      class="OroCRMTaskBundle:Task",
     *      permission="DELETE"
     * )
     */
    public function deleteAction(Task $id)
    {
        $this->getDoctrine()->getManager()->remove($id);
        $this->getDoctrine()->getManager()->flush();

        return $this->handleView($this->view(null, Codes::HTTP_NO_CONTENT));
    }

    /**
     * @return User|null
     */
    protected function getCurrentUser()
    {
        $securityToken = $this->get('security.context')->getToken();
        return $securityToken ? $securityToken->getUser() : null;

    }

    /**
     * @param Task $task
     * @return FormInterface
     */
    protected function submitTask(Task $task)
    {
        $data = $this->get('request')->request->get('task');

        $form = $this->createForm($this->getFormType(), $task);
        $form->submit($data);

        if ($form->isValid()) {
            $this->getDoctrine()->getManager()->persist($task);
            $this->getDoctrine()->getManager()->flush();
        }

        return $form;
    }

    /**
     * @return TaskRepository
     */
    protected function getTaskRepository()
    {
        return $this->getDoctrine()->getRepository('OroCRMTaskBundle:Task');
    }

    /**
     * @return SecurityFacade
     */
    protected function getSecurityFacade()
    {
        return $this->get('oro_security.security_facade');
    }

    /**
     * @return AclHelper
     */
    protected function getOrmAclHelper()
    {
        return $this->get('oro_security.acl_helper');
    }

    /**
     * @return TaskApiType
     */
    protected function getFormType()
    {
        return $this->get('orocrm_task.form.type.api_task');
    }
}
