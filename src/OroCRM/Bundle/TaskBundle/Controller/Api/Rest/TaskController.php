<?php

namespace OroCRM\Bundle\TaskBundle\Controller\Api\Rest;

use FOS\Rest\Util\Codes;
use FOS\RestBundle\Controller\Annotations\NamePrefix;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Routing\ClassResourceInterface;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;
use OroCRM\Bundle\TaskBundle\Entity\Task;

/**
 * @RouteResource("task")
 * @NamePrefix("orocrm_api_")
 */
class TaskController extends FOSRestController implements ClassResourceInterface
{
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
}
