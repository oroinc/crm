<?php

namespace Oro\Bundle\ChannelBundle\Controller\Api\Rest;

use FOS\RestBundle\Controller\Annotations\QueryParam;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Oro\Bundle\ChannelBundle\Entity\Manager\CustomerSearchApiEntityManager;
use Oro\Bundle\SoapBundle\Controller\Api\Rest\RestGetController;
use Oro\Bundle\SoapBundle\Request\Parameters\Filter\StringToArrayParameterFilter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * REST API controller to find the Customer entities.
 */
class CustomerSearchController extends RestGetController
{
    /**
     * Search customers.
     *
     * @ApiDoc(
     *      description="Search customers",
     *      resource=true
     * )
     *
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
        description: 'Number of items per page. Defaults to 10.',
        nullable: true
    )]
    #[QueryParam(
        name: 'search',
        requirements: '.+',
        description: 'The search string.',
        nullable: true
    )]
    #[QueryParam(
        name: 'dataChannel',
        requirements: '.+',
        description: 'One or several channel ids separated by comma.',
        nullable: true
    )]
    public function cgetAction(Request $request)
    {
        $page     = (int) $request->get('page', 1);
        $limit    = (int) $request->get('limit', self::ITEMS_PER_PAGE);
        $search   = $request->get('search', '');
        $criteria = null;

        if ($request->get('dataChannel')) {
            $criteria = $this->getFilterCriteria(
                $this->getSupportedQueryParameters(__FUNCTION__),
                [
                    'dataChannel' => new StringToArrayParameterFilter()
                ],
                [
                    'dataChannel' => 'integer.dataChannelId'
                ]
            );
        }

        $data = $this->getManager()->getSearchResult($page, $limit, $search, $criteria);

        return $this->buildResponse($data['result'], self::ACTION_LIST, $data);
    }

    /**
     * {@inheritdoc}
     */
    protected function getSupportedQueryParameters($methodName)
    {
        $skipParameters = ['search'];

        return array_diff(
            parent::getSupportedQueryParameters($methodName),
            $skipParameters
        );
    }

    /**
     * Gets the API entity manager
     *
     * @return CustomerSearchApiEntityManager
     */
    public function getManager()
    {
        return $this->container->get('oro_channel.manager.customer_search.api');
    }
}
