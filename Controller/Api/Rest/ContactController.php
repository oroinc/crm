<?php

namespace Oro\Bundle\ContactBundle\Controller\Api\Rest;

use FOS\RestBundle\Controller\Annotations\NamePrefix;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Oro\Bundle\UserBundle\Annotation\Acl;
use Oro\Bundle\UserBundle\Annotation\AclAncestor;

use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Routing\ClassResourceInterface;
use Oro\Bundle\SoapBundle\Form\Handler\ApiFormHandler;
use Oro\Bundle\SoapBundle\Controller\Api\Rest\FlexibleRestController;
use Oro\Bundle\SoapBundle\Entity\Manager\ApiFlexibleEntityManager;

/**
 * @RouteResource("contact")
 * @NamePrefix("oro_api_")
 */
class ContactController extends FlexibleRestController implements ClassResourceInterface
{
    /**
     * REST GET list
     *
     * @QueryParam(name="page", requirements="\d+", nullable=true, description="Page number, starting from 1. Defaults to 1.")
     * @QueryParam(name="limit", requirements="\d+", nullable=true, description="Number of items per page. defaults to 10.")
     * @ApiDoc(
     *      description="Get all contacts items",
     *      resource=true
     * )
     * @AclAncestor("oro_contact_list")
     * @return Response
     */
    public function cgetAction()
    {
        return $this->handleGetListRequest();
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
     * @AclAncestor("oro_contact_view")
     * @return Response
     */
    public function getAction($id)
    {
        return $this->handleGetRequest($id);
    }

    /**
     * REST PUT
     *
     * @param int $id Contact item id
     *
     * @ApiDoc(
     *      description="Update contact",
     *      resource=true
     * )
     * @AclAncestor("oro_contact_update")
     * @return Response
     */
    public function putAction($id)
    {
        return $this->handlePutRequest($id);
    }

    /**
     * Create new contact
     *
     * @ApiDoc(
     *      description="Create new contact",
     *      resource=true
     * )
     * @AclAncestor("oro_contact_create")
     */
    public function postAction()
    {
        return $this->handlePostRequest();
    }

    /**
     * REST DELETE
     *
     * @param int $id
     *
     * @ApiDoc(
     *      description="Delete Contact",
     *      resource=true
     * )
     * @Acl(
     *      id="oro_contact_delete",
     *      name="Delete contact",
     *      description="Delete contact",
     *      parent="oro_contact"
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
     * @return ApiFlexibleEntityManager
     */
    protected function getManager()
    {
        return $this->get('oro_contact.contact.manager.api');
    }

    /**
     * @return Form
     */
    protected function getForm()
    {
        return $this->get('oro_contact.form.contact.api');
    }

    /**
     * @return ApiFormHandler
     */
    protected function getFormHandler()
    {
        return $this->get('oro_contact.form.handler.contact.api');
    }
}
