<?php

namespace OroCRM\Bundle\MagentoBundle\Controller\Api\Rest;

use Symfony\Component\HttpFoundation\Response;

use FOS\RestBundle\Controller\Annotations\NamePrefix;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\RestBundle\Controller\Annotations\RouteResource;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\SoapBundle\Controller\Api\Rest\RestController;
use Oro\Bundle\SoapBundle\Request\Parameters\Filter\HttpDateTimeParameterFilter;

/**
 * @RouteResource("magentocustomer")
 * @NamePrefix("oro_api_")
 */
class MagentoCustomerController extends RestController implements ClassResourceInterface
{
    /**
     * {@inheritdoc}
     */
    public function getManager()
    {
        return $this->get('orocrm_magento.customer.manager.api');
    }

    /**
     * {@inheritdoc}
     */
    public function getForm()
    {
        return $this->get('orocrm_magento.form.customer.api');
    }

    /**
     * {@inheritdoc}
     */
    public function getFormHandler()
    {
        return $this->get('orocrm_magento.form.handler.customer.api');
    }

    /**
     * Get all magento customers.
     *
     * @QueryParam(
     *     name="startCreatedAt",
     *     requirements="\d{4}(-\d{2}(-\d{2}([T ]\d{2}:\d{2}(:\d{2}(\.\d+)?)?(Z|([-+]\d{2}(:?\d{2})?))?)?)?)?",
     *     nullable=true,
     *     description="Date in RFC 3339 format. For example: 2009-11-05T13:15:30Z, 2008-07-01T22:35:17+08:00"
     * )
     * @QueryParam(
     *     name="endCreatedAt",
     *     requirements="\d{4}(-\d{2}(-\d{2}([T ]\d{2}:\d{2}(:\d{2}(\.\d+)?)?(Z|([-+]\d{2}(:?\d{2})?))?)?)?)?",
     *     nullable=true,
     *     description="Date in RFC 3339 format. For example: 2009-11-05T13:15:30Z, 2008-07-01T22:35:17+08:00"
     * )
     * @QueryParam(
     *     name="startUpdatedAt",
     *     requirements="\d{4}(-\d{2}(-\d{2}([T ]\d{2}:\d{2}(:\d{2}(\.\d+)?)?(Z|([-+]\d{2}(:?\d{2})?))?)?)?)?",
     *     nullable=true,
     *     description="Date in RFC 3339 format. For example: 2009-11-05T13:15:30Z, 2008-07-01T22:35:17+08:00,
     *                  parameter start means that you want to use >= (more or equal) comparison, prefix end means
     *                  <= (less or equal)"
     * )
     * @QueryParam(
     *     name="endUpdatedAt",
     *     requirements="\d{4}(-\d{2}(-\d{2}([T ]\d{2}:\d{2}(:\d{2}(\.\d+)?)?(Z|([-+]\d{2}(:?\d{2})?))?)?)?)?",
     *     nullable=true,
     *     description="Date in RFC 3339 format. For example: 2009-11-05T13:15:30Z, 2008-07-01T22:35:17+08:00,
     *       parameter start means that you want to use >= (more or equal) comparison, prefix end means
     *       <= (less or equal)"
     * )
     * @QueryParam(
     *     name="page", requirements="\d+", nullable=true, description="Page number, starting from 1. Defaults to 1."
     * )
     * @QueryParam(
     *     name="limit", requirements="\d+", nullable=true, description="Number of items per page. defaults to 10."
     * )
     * @ApiDoc(
     *      description="Get all magento customers",
     *      resource=true
     * )
     * @AclAncestor("orocrm_magento_customer_view")
     *
     * @return Response
     */
    public function cgetAction()
    {
        $page  = (int)$this->getRequest()->get('page', 1);
        $limit = (int)$this->getRequest()->get('limit', self::ITEMS_PER_PAGE);

        $dateParamFilter = new HttpDateTimeParameterFilter();
        $parameters      = [
            'createdAt' => $dateParamFilter,
            'updatedAt' => $dateParamFilter,
        ];

        $criteria = $this->getFilterCriteria(
            $this->getSupportedQueryParameters(__FUNCTION__),
            $parameters
        );

        return $this->handleGetListRequest($page, $limit, $criteria);
    }

    /**
     * Get magento customer.
     *
     * @param string $id
     *
     * @ApiDoc(
     *      description="Get magento customer",
     *      resource=true
     * )
     * @AclAncestor("orocrm_magento_customer_view")
     *
     * @return Response
     */
    public function getAction($id)
    {
        return $this->handleGetRequest($id);
    }

    /**
     * Create new magento customer.
     *
     * @ApiDoc(
     *      description="Create new magento customer",
     *      resource=true
     * )
     * @AclAncestor("orocrm_magento_customer_create")
     *
     */
    public function postAction()
    {
        return $this->handleCreateRequest();
    }

    /**
     * Update magento customer.
     *
     * @param int $id Customer item id
     *
     * @ApiDoc(
     *      description="Update magento customer",
     *      resource=true
     * )
     * @AclAncestor("orocrm_magento_customer_update")
     *
     * @return Response
     */
    public function putAction($id)
    {
        return $this->handleUpdateRequest($id);
    }

    /**
     * Delete magento customer.
     *
     * @param int $id
     *
     * @ApiDoc(
     *      description="Delete magento customer",
     *      resource=true
     * )
     * @Acl(
     *      id="orocrm_magento_customer_delete",
     *      type="entity",
     *      permission="DELETE",
     *      class="OroCRMMagentoBundle:Customer"
     * )
     * @return Response
     */
    public function deleteAction($id)
    {
        return $this->handleDeleteRequest($id);
    }

    /**
     * {@inheritdoc}
     */
    protected function filterQueryParameters(array $supportedParameters)
    {
        $filteredParameters = parent::filterQueryParameters($supportedParameters);
        $result             = [];

        foreach ($filteredParameters as $key => $value) {
            $startPosition = strpos($key, 'start');
            $endPosition   = strpos($key, 'end');

            if ($startPosition === 0) {
                $realKey          = lcfirst(substr($key, $startPosition + strlen('start')));
                $result[$realKey] = ['>=', $value[1]];
            } elseif ($endPosition === 0) {
                $realKey          = lcfirst(substr($key, $endPosition + strlen('end')));
                $result[$realKey] = ['<=', $value[1]];
            } else {
                $result[$key] = $value;
            }
        }
        return $result;
    }
}
