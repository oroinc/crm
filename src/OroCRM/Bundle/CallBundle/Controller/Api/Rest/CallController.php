<?php

namespace OroCRM\Bundle\CallBundle\Controller\Api\Rest;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Response;

use FOS\RestBundle\Controller\Annotations\NamePrefix;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Routing\ClassResourceInterface;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;

use Oro\Bundle\SoapBundle\Form\Handler\ApiFormHandler;
use Oro\Bundle\SoapBundle\Controller\Api\Rest\RestController;
use Oro\Bundle\SoapBundle\Entity\Manager\ApiEntityManager;

/**
 * @RouteResource("call")
 * @NamePrefix("oro_api_")
 */
class CallController extends RestController implements ClassResourceInterface
{
    /**
     * REST GET item
     *
     * @param string $id
     *
     * @ApiDoc(
     *      description="Get call item",
     *      resource=true
     * )
     * @AclAncestor("orocrm_call_index")
     * @return Response
     */
    public function getAction($id)
    {
        return $this->handleGetRequest($id);
    }

    /**
     * REST PUT
     *
     * @param int $id call item id
     *
     * @ApiDoc(
     *      description="Update call",
     *      resource=true
     * )
     * @AclAncestor("orocrm_call_update")
     * @return Response
     */
    public function putAction($id)
    {
        return $this->handleUpdateRequest($id);
    }

    /**
     * Create new call
     *
     * @ApiDoc(
     *      description="Create new call",
     *      resource=true
     * )
     * @AclAncestor("orocrm_call_create")
     */
    public function postAction()
    {
        return $this->handleCreateRequest();
    }

    /**
     * REST DELETE
     *
     * @param int $id
     *
     * @ApiDoc(
     *      description="Delete call",
     *      resource=true
     * )
     * @Acl(
     *      id="orocrm_call_delete",
     *      type="entity",
     *      permission="DELETE",
     *      class="OroCRMCallBundle:Call"
     * )
     * @return Response
     */
    public function deleteAction($id)
    {
        return $this->handleDeleteRequest($id);
    }

    /**
     * Get entity Manager
     *
     * @return ApiEntityManager
     */
    public function getManager()
    {
        return $this->get('orocrm_call.call.manager.api');
    }

    /**
     * @return FormInterface
     */
    public function getForm()
    {
        return $this->get('orocrm_call.form.call.api');
    }

    /**
     * @return ApiFormHandler
     */
    public function getFormHandler()
    {
        return $this->get('orocrm_call.form.handler.call.api');
    }
}
