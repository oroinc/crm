<?php

namespace OroCRM\Bundle\TaskBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;

use OroCRM\Bundle\AccountBundle\Entity\Account;
use OroCRM\Bundle\TaskBundle\Entity\Task;

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
     *      id="orocrm_task_create",
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
     * @Route("/widget/account-tasks/{id}", name="orocrm_account_tasks_widget", requirements={"id"="\d+"})
     * @AclAncestor("orocrm_task_index")
     * @Template
     */
    public function accountTasksAction(Account $account)
    {
        return array('account' => $account);
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

        return $this->update($task);
    }

    /**
     * @param Task $task
     * @return array
     */
    protected function update(Task $task)
    {
        $request = $this->getRequest();
        $form = $this->createForm($this->getFormType(), $task);

        if ($request->isMethod('POST')) {
            $form->submit($request);
            if ($form->isValid()) {
                $this->getDoctrine()->getManager()->persist($task);
                $this->getDoctrine()->getManager()->flush();

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

        return array(
            'entity' => $task,
            'form' => $form->createView()
        );
    }

    /**
     * @return \OroCRM\Bundle\TaskBundle\Form\Type\TaskType
     */
    protected function getFormType()
    {
        return $this->get('orocrm_task.form.type.task');
    }

    /**
     * @Route("/view/{id}", name="orocrm_task_view", requirements={"id"="\d+"})
     * @Acl(
     *      id="orocrm_task_view",
     *      type="entity",
     *      class="OroCRMTaskBundle:Task",
     *      permission="VIEW"
     * )
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
}
