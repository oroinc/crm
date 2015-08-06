<?php

namespace OroCRM\Bundle\MagentoBundle\Controller\Api\Rest;

use Symfony\Component\HttpFoundation\Response;

use FOS\RestBundle\Controller\Annotations\NamePrefix;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Controller\Annotations\RouteResource;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\SoapBundle\Controller\Api\Rest\RestController;

/**
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
        return $this->get('orocrm_magento.order.manager.api');
    }

    /**
     * {@inheritdoc}
     */
    public function getForm()
    {
        return $this->get('orocrm_magento.form.order.api');
    }

    /**
     * {@inheritdoc}
     */
    public function getFormHandler()
    {
        return $this->get('orocrm_magento.form.handler.order.api');
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
     * @AclAncestor("orocrm_magento_order_view")
     *
     * @return Response
     */
    public function cgetAction()
    {
        $page  = (int)$this->getRequest()->get('page', 1);
        $limit = (int)$this->getRequest()->get('limit', self::ITEMS_PER_PAGE);

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
     *      id="orocrm_magento_order_create",
     *      type="entity",
     *      permission="CREATE",
     *      class="OroCRMMagentoBundle:Order"
     * )
     */
    public function postAction()
    {
        return $this->handleCreateRequest();
    }

    /**
     * Get order.
     *
     * @param string $id
     *
     * @ApiDoc(
     *      description="Get order",
     *      resource=true
     * )
     * @AclAncestor("orocrm_magento_order_view")
     *
     * @return Response
     */
    public function getAction($id)
    {
        return $this->handleGetRequest($id);
    }

    /**
     * Update order.
     *
     * @param int $id Order id
     *
     * @ApiDoc(
     *      description="Update order",
     *      resource=true
     * )
     * @Acl(
     *      id="orocrm_magento_order_update",
     *      type="entity",
     *      permission="EDIT",
     *      class="OroCRMMagentoBundle:Order"
     * )
     * @return Response
     */
    public function putAction($id)
    {
        return $this->handleUpdateRequest($id);
    }

    /**
     * Delete order.
     *
     * @param int $id
     *
     * @ApiDoc(
     *      description="Delete order",
     *      resource=true
     * )
     * @Acl(
     *      id="orocrm_magento_order_delete",
     *      type="entity",
     *      permission="DELETE",
     *      class="OroCRMMagentoBundle:Order"
     * )
     * @return Response
     */
    public function deleteAction($id)
    {
        return $this->handleDeleteRequest($id);
    }
}
