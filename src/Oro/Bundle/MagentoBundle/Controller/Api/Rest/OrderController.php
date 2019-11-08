<?php

namespace Oro\Bundle\MagentoBundle\Controller\Api\Rest;

use FOS\RestBundle\Controller\Annotations\Delete;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\NamePrefix;
use FOS\RestBundle\Controller\Annotations\Put;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\SoapBundle\Controller\Api\Rest\RestController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * API CRUD controller for Order entity.
 *
 * @RouteResource("order")
 * @NamePrefix("oro_api_")
 */
class OrderController extends RestController
{
    /**
     * {@inheritdoc}
     */
    public function getManager()
    {
        return $this->get('oro_magento.order.manager.api');
    }

    /**
     * {@inheritdoc}
     */
    public function getForm()
    {
        return $this->get('oro_magento.form.order.api');
    }

    /**
     * {@inheritdoc}
     */
    public function getFormHandler()
    {
        return $this->get('oro_magento.form.handler.order.api');
    }

    /**
     * Get all orders.
     *
     * @QueryParam(
     *     name="page", requirements="\d+", nullable=true, description="Page number, starting from 1. Defaults to 1."
     * )
     * @QueryParam(
     *     name="limit", requirements="\d+", nullable=true, description="Number of items per page. defaults to 10."
     * )
     * @ApiDoc(
     *      description="Get all orders",
     *      resource=true
     * )
     * @AclAncestor("oro_magento_order_view")
     * @param Request $request
     * @return Response
     */
    public function cgetAction(Request $request)
    {
        $page  = (int)$request->get('page', 1);
        $limit = (int)$request->get('limit', self::ITEMS_PER_PAGE);

        return $this->handleGetListRequest($page, $limit);
    }

    /**
     * Create new order.
     *
     * @ApiDoc(
     *      description="Create new order",
     *      resource=true
     * )
     * @Acl(
     *      id="oro_magento_order_create",
     *      type="entity",
     *      permission="CREATE",
     *      class="OroMagentoBundle:Order"
     * )
     */
    public function postAction()
    {
        return $this->handleCreateRequest();
    }

    /**
     * Get order.
     *
     * @param int $id
     *
     * @Get(requirements={"id"="\d+"})
     *
     * @ApiDoc(
     *      description="Get order",
     *      resource=true
     * )
     * @AclAncestor("oro_magento_order_view")
     *
     * @return Response
     */
    public function getAction(int $id)
    {
        return $this->handleGetRequest($id);
    }

    /**
     * Update order.
     *
     * @param int $id Order id
     *
     * @Put(requirements={"id"="\d+"})
     *
     * @ApiDoc(
     *      description="Update order",
     *      resource=true
     * )
     * @Acl(
     *      id="oro_magento_order_update",
     *      type="entity",
     *      permission="EDIT",
     *      class="OroMagentoBundle:Order"
     * )
     * @return Response
     */
    public function putAction(int $id)
    {
        return $this->handleUpdateRequest($id);
    }

    /**
     * Delete order.
     *
     * @param int $id
     *
     * @Delete(requirements={"id"="\d+"})
     *
     * @ApiDoc(
     *      description="Delete order",
     *      resource=true
     * )
     * @Acl(
     *      id="oro_magento_order_delete",
     *      type="entity",
     *      permission="DELETE",
     *      class="OroMagentoBundle:Order"
     * )
     * @return Response
     */
    public function deleteAction(int $id)
    {
        return $this->handleDeleteRequest($id);
    }
}
