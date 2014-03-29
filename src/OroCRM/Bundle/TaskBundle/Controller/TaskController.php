<?php

namespace OroCRM\Bundle\TaskBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;

use Oro\Bundle\UserBundle\Entity\User;
use OroCRM\Bundle\AccountBundle\Entity\Account;
use OroCRM\Bundle\ContactBundle\Entity\Contact;
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
        return [];
    }

    /**
     * @Route("/widget/sidebar-tasks/{perPage}", name="orocrm_task_widget_sidebar_tasks", defaults={"perPage" = 10})
     * @AclAncestor("orocrm_task_view")
     * @Template("OroCRMTaskBundle:Task/widget:tasksWidget.html.twig")
     */
    public function tasksWidgetAction($perPage)
    {
        /**
         * @var TaskRepository $repository
         */
        $repository = $this->getRepository('OroCRM\Bundle\TaskBundle\Entity\Task');
        $id = $this->getUser()->getId();
        $perPage = (int)$perPage;
        $tasks = $repository->getTaskAssignedTo($id, $perPage);

        return array('tasks' => $tasks);
    }

    /**
     * @Route("/widget/account-tasks/{id}", name="orocrm_task_widget_account_tasks", requirements={"id"="\d+"})
     * @AclAncestor("orocrm_task_view")
     * @Template
     */
    public function accountTasksAction(Account $account)
    {
        return array('account' => $account);
    }

    /**
     * @Route("/widget/user-tasks/{id}", name="orocrm_task_widget_user_tasks", requirements={"id"="\d+"})
     * @AclAncestor("orocrm_task_view")
     * @Template
     */
    public function userTasksAction(User $user)
    {
        return array('user' => $user);
    }

    /**
     * @Route("/widget/contact-tasks/{id}", name="orocrm_task_widget_contact_tasks", requirements={"id"="\d+"})
     * @AclAncestor("orocrm_task_view")
     * @Template
     */
    public function contactTasksAction(Contact $contact)
    {
        return array('contact' => $contact);
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

        $accountId = $this->getRequest()->get('accountId');
        if ($accountId) {
            $account = $this->getRepository('OroCRMAccountBundle:Account')->find($accountId);
            if (!$account) {
                throw new NotFoundHttpException(sprintf('Account with ID %s is not found', $accountId));
            }
            $task->setRelatedAccount($account);
        }

        $contactId = $this->getRequest()->get('contactId');
        if ($contactId) {
            $contact = $this->getRepository('OroCRMContactBundle:Contact')->find($contactId);
            if (!$contact) {
                throw new NotFoundHttpException(sprintf('Contact with ID %s is not found', $contactId));
            }
            $task->setRelatedContact($contact);
        }

        $assignedToId = $this->getRequest()->get('assignedToId');
        if ($assignedToId) {
            $assignedTo = $this->getRepository('OroUserBundle:User')->find($assignedToId);
            if (!$assignedTo) {
                throw new NotFoundHttpException(sprintf('User with ID %s is not found', $assignedToId));
            }
            $task->setOwner($assignedTo);
        }

        $reporter = $this->getCurrentUser();
        if ($reporter) {
            $task->setReporter($reporter);
        }

        return $this->update($task);
    }

    /**
     * @return User
     */
    public function getCurrentUser()
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
        return $this->update($task);
    }

    /**
     * @param Task $task
     * @return array
     */
    protected function update(Task $task)
    {
        $saved = false;
        $request = $this->getRequest();
        $form = $this->createForm($this->getFormType(), $task);

        if ($request->isMethod('POST')) {
            $form->submit($request);
            if ($form->isValid()) {
                $this->getDoctrine()->getManager()->persist($task);
                $this->getDoctrine()->getManager()->flush();

                $saved =  true;

                if (!$this->getRequest()->request->has('_widgetContainer')) {
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
            }
        }

        return array(
            'saved' => $saved,
            'entity' => $task,
            'form' => $form->createView()
        );
    }

    /**
     * @return TaskType
     */
    protected function getFormType()
    {
        return $this->get('orocrm_task.form.type.task');
    }

    /**
     * @param string $entityName
     * @return \Doctrine\Common\Persistence\ObjectRepository
     */
    protected function getRepository($entityName)
    {
        return $this->getDoctrine()->getRepository($entityName);
    }
}
