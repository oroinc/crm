<?php

namespace OroCRM\Bundle\TaskBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;

use OroCRM\Bundle\AccountBundle\Entity\Account;

/**
 * @Route("/task")
 */
class TaskController extends Controller
{
    /**
     * @Route("/widget/account-tasks/{id}", name="orocrm_account_tasks_widget", requirements={"id"="\d+"})
     * @Template()
     */
    public function accountTasksAction(Account $account)
    {
        //todo: add acl
        return array('account' => $account);
    }

    /**
     * @Route("/create", name="orocrm_task_create")
     * @Template
     * @Acl(
     *      id="orocrm_task_create",
     *      type="entity",
     *      permission="CREATE",
     *      class="OroCRMTaskBundle:Task"
     * )
     */
    public function createAction()
    {
        // @TODO Dummy method to create placeholder buttons for task creation
    }
}
