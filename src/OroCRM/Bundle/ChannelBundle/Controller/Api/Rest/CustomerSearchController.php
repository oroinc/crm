<?php

namespace OroCRM\Bundle\ChannelBundle\Controller\Api\Rest;

use Symfony\Component\HttpFoundation\Response;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use FOS\RestBundle\Controller\Annotations\NamePrefix;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\QueryParam;

use Oro\Bundle\SoapBundle\Controller\Api\Rest\RestGetController;
use OroCRM\Bundle\ChannelBundle\Entity\Manager\CustomerSearchApiEntityManager;

/**
 * @RouteResource("search_customer")
 * @NamePrefix("oro_api_")
 */
class CustomerSearchController extends RestGetController
{
    /**
     * Search customers.
     *
     *
     * @Get("/customers/search", name="")
     *
     * @QueryParam(
     *      name="page",
     *      requirements="\d+",
     *      nullable=true,
     *      description="Page number, starting from 1. Defaults to 1."
     * )
     * @QueryParam(
     *      name="limit",
     *      requirements="\d+",
     *      nullable=true,
     *      description="Number of items per page. Defaults to 10."
     * )
     * @QueryParam(
     *     name="search",
     *     requirements=".+",
     *     nullable=true,
     *     description="The search string."
     * )
     *
     * @ApiDoc(
     *      description="Search customers",
     *      resource=true
     * )
     *
     * @return Response
     */
    public function cgetAction()
    {
        $page   = (int)$this->getRequest()->get('page', 1);
        $limit  = (int)$this->getRequest()->get('limit', self::ITEMS_PER_PAGE);
        $search = $this->getRequest()->get('search', '');

        $data = $this->getManager()->getSearchResult($page, $limit, $search);

        return $this->buildResponse($data['result'], self::ACTION_LIST, $data);
    }

    /**
     * Gets the API entity manager
     *
     * @return CustomerSearchApiEntityManager
     */
    public function getManager()
    {
        return $this->container->get('orocrm_channel.manager.customer_search.api');
    }
}
