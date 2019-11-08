<?php

namespace Oro\Bundle\ContactBundle\Controller\Api\Rest;

use FOS\RestBundle\Controller\Annotations\Delete;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\NamePrefix;
use FOS\RestBundle\Controller\Annotations\Put;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use FOS\RestBundle\Routing\ClassResourceInterface;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\SoapBundle\Controller\Api\Rest\RestController;
use Oro\Bundle\SoapBundle\Entity\Manager\ApiEntityManager;
use Oro\Bundle\SoapBundle\Form\Handler\ApiFormHandler;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * API CRUD controller for ContactGroup entity.
 *
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
     * @AclAncestor("oro_contact_group_view")
     * @param Request $request
     * @return Response
     */
    public function cgetAction(Request $request)
    {
        $page = (int)$request->get('page', 1);
        $limit = (int)$request->get('limit', self::ITEMS_PER_PAGE);

        return $this->handleGetListRequest($page, $limit);
    }

    /**
     * REST GET item
     *
     * @param int $id
     *
     * @Get(requirements={"id"="\d+"})
     *
     * @ApiDoc(
     *      description="Get contact item",
     *      resource=true
     * )
     * @AclAncestor("oro_contact_group_view")
     * @return Response
     */
    public function getAction(int $id)
    {
        return $this->handleGetRequest($id);
    }

    /**
     * REST PUT
     *
     * @param int $id
     *
     * @Put(requirements={"id"="\d+"})
     *
     * @ApiDoc(
     *      description="Update contact group",
     *      resource=true
     * )
     * @AclAncestor("oro_contact_group_update")
     * @return Response
     */
    public function putAction(int $id)
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
     * @AclAncestor("oro_contact_group_create")
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
     * @Delete(requirements={"id"="\d+"})
     *
     * @ApiDoc(
     *      description="Delete Contact Group",
     *      resource=true
     * )
     * @Acl(
     *      id="oro_contact_group_delete",
     *      type="entity",
     *      permission="DELETE",
     *      class="OroContactBundle:Group"
     * )
     * @return Response
     */
    public function deleteAction(int $id)
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
        return $this->get('oro_contact.group.manager.api');
    }

    /**
     * @return FormInterface
     */
    public function getForm()
    {
        return $this->get('oro_contact.form.group.api');
    }

    /**
     * @return ApiFormHandler
     */
    public function getFormHandler()
    {
        return $this->get('oro_contact.form.handler.group.api');
    }
}
