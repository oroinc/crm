<?php

namespace Oro\Bundle\SalesBundle\Controller\Api\Rest;

use FOS\RestBundle\Controller\Annotations\QueryParam;
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
 * REST API CRUD controller for Lead entity.
 */
class LeadController extends RestController
{
    /**
     * REST GET list
     *
     * @QueryParam(
     *      name="page",
     *      requirements="\d+",
     *      nullable=true,
     *      description="Page number, starting from 1. Defaults to 1."
     * )
     * @QueryParam(
     *      name="limit",
     *      requirements="\d+", nullable=true,
     *      description="Number of items per page. defaults to 10."
     * )
     * @ApiDoc(
     *      description="Get all lead items",
     *      resource=true
     * )
     * @AclAncestor("oro_sales_lead_view")
     * @param Request $request
     * @return Response
     */
    public function cgetAction(Request $request)
    {
        $page = (int) $request->get('page', 1);
        $limit = (int) $request->get('limit', self::ITEMS_PER_PAGE);

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
     * @AclAncestor("oro_sales_lead_view")
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
     * @ApiDoc(
     *      description="Update lead",
     *      resource=true
     * )
     * @AclAncestor("oro_sales_lead_update")
     * @return Response
     */
    public function putAction(int $id)
    {
        return $this->handleUpdateRequest($id);
    }

    /**
     * Create new lead
     *
     * @ApiDoc(
     *      description="Create new lead",
     *      resource=true
     * )
     * @AclAncestor("oro_sales_lead_create")
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
     *      description="Delete lead",
     *      resource=true
     * )
     * @Acl(
     *      id="oro_sales_lead_delete",
     *      type="entity",
     *      permission="DELETE",
     *      class="OroSalesBundle:Lead"
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
        return $this->get('oro_sales.lead.manager.api');
    }

    /**
     * @return FormInterface
     */
    public function getForm()
    {
        return $this->get('oro_sales.lead.form.api');
    }

    /**
     * @return ApiFormHandler
     */
    public function getFormHandler()
    {
        return $this->get('oro_sales.lead.form.handler.api');
    }

    /**
     * {@inheritDoc}
     *
     * overriden because of new updateHandler requirements ->process(entity, form, request)
     */
    protected function processForm($entity)
    {
        $this->fixRequestAttributes($entity);

        $result = $this->getFormHandler()->process(
            $entity,
            $this->getForm(),
            $this->get('request_stack')->getCurrentRequest()
        );
        if (\is_object($result) || null === $result) {
            return $result;
        }

        // some form handlers may return true/false rather than saved entity
        return $result ? $entity : null;
    }
}
