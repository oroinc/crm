<?php

namespace Oro\Bundle\SalesBundle\Controller\Api\Rest;

use FOS\RestBundle\Controller\Annotations\Delete;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\NamePrefix;
use FOS\RestBundle\Controller\Annotations\Put;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use FOS\RestBundle\Routing\ClassResourceInterface;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Oro\Bundle\AddressBundle\Utils\AddressApiUtils;
use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\SoapBundle\Controller\Api\Rest\RestController;
use Oro\Bundle\SoapBundle\Entity\Manager\ApiEntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * API CRUD controller for B2BCustomer entity.
 *
 * @RouteResource("b2bcustomer")
 * @NamePrefix("oro_api_")
 */
class B2bCustomerController extends RestController implements ClassResourceInterface
{
    /**
     * Get B2B customers.
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
     *      description="Get business customers",
     *      resource=true
     * )
     * @AclAncestor("oro_sales_b2bcustomer_view")
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
     * Get B2B customer.
     *
     * @param int $id
     *
     * @Get(requirements={"id"="\d+"})
     *
     * @ApiDoc(
     *      description="Get business customer",
     *      resource=true
     * )
     * @AclAncestor("oro_sales_b2bcustomer_view")
     * @return Response
     */
    public function getAction(int $id)
    {
        return $this->handleGetRequest($id);
    }

    /**
     * Update B2B customer.
     *
     * @param int $id
     *
     * @Put(requirements={"id"="\d+"})
     *
     * @ApiDoc(
     *      description="Update business customer",
     *      resource=true
     * )
     * @AclAncestor("oro_sales_b2bcustomer_update")
     * @return Response
     */
    public function putAction(int $id)
    {
        return $this->handleUpdateRequest($id);
    }

    /**
     * Create new B2B customer.
     *
     * @ApiDoc(
     *      description="Create new business customer",
     *      resource=true
     * )
     * @AclAncestor("oro_sales_b2bcustomer_create")
     */
    public function postAction()
    {
        return $this->handleCreateRequest();
    }

    /**
     * Delete B2B customer.
     *
     * @param int $id
     *
     * @Delete(requirements={"id"="\d+"})
     *
     * @ApiDoc(
     *      description="Delete business customer",
     *      resource=true
     * )
     * @Acl(
     *      id="oro_sales_b2bcustomer_delete",
     *      type="entity",
     *      permission="DELETE",
     *      class="OroSalesBundle:B2bCustomer"
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
        return $this->get('oro_sales.b2bcustomer.manager.api');
    }

    /**
     * {@inheritdoc}
     */
    public function getForm()
    {
        return $this->get('oro_sales.b2bcustomer.form.api');
    }

    /**
     * {@inheritdoc}
     */
    public function getFormHandler()
    {
        return $this->get('oro_sales.b2bcustomer.form.handler.api');
    }

    /**
     * {@inheritdoc}
     */
    protected function fixRequestAttributes($entity)
    {
        $formAlias = 'b2bcustomer';
        $request = $this->get('request_stack')->getCurrentRequest();
        $customerData = $request->request->get($formAlias);

        // - convert country name to country code (as result we accept both the code and the name)
        //   also it will be good to accept ISO3 code in future, need to be discussed with product owners
        // - convert region name to region code (as result we accept the combined code, code and name)
        // - move region name to region_text field for unknown region
        if (array_key_exists('shippingAddress', $customerData)) {
            AddressApiUtils::fixAddress($customerData['shippingAddress'], $this->get('doctrine.orm.entity_manager'));
            $request->request->set($formAlias, $customerData);
        }
        if (array_key_exists('billingAddress', $customerData)) {
            AddressApiUtils::fixAddress($customerData['billingAddress'], $this->get('doctrine.orm.entity_manager'));
            $request->request->set($formAlias, $customerData);
        }

        parent::fixRequestAttributes($entity);
    }
}
