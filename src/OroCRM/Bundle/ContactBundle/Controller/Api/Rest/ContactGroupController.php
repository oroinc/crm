<?php

namespace OroCRM\Bundle\ContactBundle\Controller\Api\Rest;

use FOS\RestBundle\Controller\Annotations\NamePrefix;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Routing\ClassResourceInterface;
use Oro\Bundle\SoapBundle\Form\Handler\ApiFormHandler;
use Oro\Bundle\SoapBundle\Controller\Api\Rest\RestController;
use Oro\Bundle\SoapBundle\Entity\Manager\ApiEntityManager;

/**
 * @RouteResource("contactgroup")
 * @NamePrefix("oro_api_")
 */
class ContactGroupController extends RestController implements ClassResourceInterface
{
    /**
     * REST GET list
     *
     * @QueryParam(
     *      name="page",
     *      requirements="\d+",
     *      nullable=true, description="Page number, starting from 1. Defaults to 1."
     * )
     * @QueryParam(
     *      name="limit",
     *      requirements="\d+",
     *      nullable=true,
     *      description="Number of items per page. defaults to 10."
     * )
     * @ApiDoc(
     *      description="Get all contact group items",
     *      resource=true
     * )
     * @AclAncestor("orocrm_contact_group_view")
     * @return Response
     */
    public function cgetAction()
    {
        $page = (int)$this->getRequest()->get('page', 1);
        $limit = (int)$this->getRequest()->get('limit', self::ITEMS_PER_PAGE);

        return $this->handleGetListRequest($page, $limit);
    }

    /**
     * REST GET item
     *
     * @param string $id
     *
     * @ApiDoc(
     *      description="Get contact item",
     *      resource=true
     * )
     * @AclAncestor("orocrm_contact_group_view")
     * @return Response
     */
    public function getAction($id)
    {
        return $this->handleGetRequest($id);
    }

    /**
     * REST PUT
     *
     * @param int $id
     *
     * @ApiDoc(
     *      description="Update contact group",
     *      resource=true
     * )
     * @AclAncestor("orocrm_contact_group_update")
     * @return Response
     */
    public function putAction($id)
    {
        return $this->handleUpdateRequest($id);
    }

    /**
     * Create new contact group
     *
     * @ApiDoc(
     *      description="Create new contact group",
     *      resource=true
     * )
     * @AclAncestor("orocrm_contact_group_create")
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
     *      description="Delete Contact Group",
     *      resource=true
     * )
     * @Acl(
     *      id="orocrm_contact_group_delete",
     *      type="entity",
     *      permission="DELETE",
     *      class="OroCRMContactBundle:Group"
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
        return $this->get('orocrm_contact.group.manager.api');
    }

    /**
     * @return FormInterface
     */
    public function getForm()
    {
        return $this->get('orocrm_contact.form.group.api');
    }

    /**
     * @return ApiFormHandler
     */
    public function getFormHandler()
    {
        return $this->get('orocrm_contact.form.handler.group.api');
    }
}
