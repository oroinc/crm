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
 * @RouteResource("cart")
 * @NamePrefix("oro_api_")
 */
class CartController extends RestController
{
    /**
     * {@inheritdoc}
     */
    public function getManager()
    {
        return $this->get('orocrm_magento.cart.manager.api');
    }

    /**
     * {@inheritdoc}
     */
    public function getForm()
    {
        return $this->get('orocrm_magento.form.cart.api');
    }

    /**
     * {@inheritdoc}
     */
    public function getFormHandler()
    {
        return $this->get('orocrm_magento.form.handler.cart.api');
    }

    /**
     * Get all carts.
     *
     * @QueryParam(
     *     name="page", requirements="\d+", nullable=true, description="Page number, starting from 1. Defaults to 1."
     * )
     * @QueryParam(
     *     name="limit", requirements="\d+", nullable=true, description="Number of items per page. defaults to 10."
     * )
     * @ApiDoc(
     *      description="Get all carts",
     *      resource=true
     * )
     * @AclAncestor("orocrm_magento_cart_view")
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
     * Create new cart.
     *
     * @ApiDoc(
     *      description="Create new cart",
     *      resource=true
     * )
     * @Acl(
     *      id="orocrm_magento_cart_create",
     *      type="entity",
     *      permission="CREATE",
     *      class="OroCRMMagentoBundle:Cart"
     * )
     */
    public function postAction()
    {
        return $this->handleCreateRequest();
    }

    /**
     * Get cart.
     *
     * @param string $id
     *
     * @ApiDoc(
     *      description="Get cart",
     *      resource=true
     * )
     * @AclAncestor("orocrm_magento_cart_view")
     *
     * @return Response
     */
    public function getAction($id)
    {
        return $this->handleGetRequest($id);
    }

    /**
     * Update cart.
     *
     * @param int $id Cart id
     *
     * @ApiDoc(
     *      description="Update cart",
     *      resource=true
     * )
     * @Acl(
     *      id="orocrm_magento_cart_update",
     *      type="entity",
     *      permission="EDIT",
     *      class="OroCRMMagentoBundle:Cart"
     * )
     * @return Response
     */
    public function putAction($id)
    {
        return $this->handleUpdateRequest($id);
    }

    /**
     * Delete cart.
     *
     * @param int $id
     *
     * @ApiDoc(
     *      description="Delete cart",
     *      resource=true
     * )
     * @Acl(
     *      id="orocrm_magento_cart_delete",
     *      type="entity",
     *      permission="DELETE",
     *      class="OroCRMMagentoBundle:Cart"
     * )
     * @return Response
     */
    public function deleteAction($id)
    {
        return $this->handleDeleteRequest($id);
    }
}
