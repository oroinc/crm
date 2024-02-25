<?php

namespace Oro\Bundle\ContactBundle\Controller\Api\Rest;

use FOS\RestBundle\Controller\Annotations\QueryParam;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Oro\Bundle\ContactBundle\Entity\Group;
use Oro\Bundle\SecurityBundle\Attribute\Acl;
use Oro\Bundle\SecurityBundle\Attribute\AclAncestor;
use Oro\Bundle\SoapBundle\Controller\Api\Rest\RestController;
use Oro\Bundle\SoapBundle\Entity\Manager\ApiEntityManager;
use Oro\Bundle\SoapBundle\Form\Handler\ApiFormHandler;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * REST API CRUD controller for ContactGroup entity.
 */
class ContactGroupController extends RestController
{
    /**
     * REST GET list
     *
     * @ApiDoc(
     *      description="Get all contact group items",
     *      resource=true
     * )
     * @param Request $request
     * @return Response
     */
    #[QueryParam(
        name: 'page',
        requirements: '\d+',
        description: 'Page number, starting from 1. Defaults to 1.',
        nullable: true
    )]
    #[QueryParam(
        name: 'limit',
        requirements: '\d+',
        description: 'Number of items per page. defaults to 10.',
        nullable: true
    )]
    #[AclAncestor('oro_contact_group_view')]
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
     * @ApiDoc(
     *      description="Get contact item",
     *      resource=true
     * )
     * @return Response
     */
    #[AclAncestor('oro_contact_group_view')]
    public function getAction(int $id)
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
     * @return Response
     */
    #[AclAncestor('oro_contact_group_update')]
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
     */
    #[AclAncestor('oro_contact_group_create')]
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
     * @return Response
     */
    #[Acl(id: 'oro_contact_group_delete', type: 'entity', class: Group::class, permission: 'DELETE')]
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
        return $this->container->get('oro_contact.group.manager.api');
    }

    /**
     * @return FormInterface
     */
    public function getForm()
    {
        return $this->container->get('oro_contact.form.group.api');
    }

    /**
     * @return ApiFormHandler
     */
    public function getFormHandler()
    {
        return $this->container->get('oro_contact.form.handler.group.api');
    }
}
