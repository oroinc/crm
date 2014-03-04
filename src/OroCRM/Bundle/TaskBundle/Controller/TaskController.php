<?php

namespace OroCRM\Bundle\TaskBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;

/**
 * @Route("/task")
 */
class TaskController extends Controller
{
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
