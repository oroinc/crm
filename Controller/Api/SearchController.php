<?php

namespace Oro\Bundle\SearchBundle\Controller\Api;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\Controller\Annotations\RouteResource;

/**
 * @RouteResource("search")
 */
class SearchController extends FOSRestController
{
    /**
     * @ApiDoc(
     *  description="Get search result",
     *  resource=true,
     *  filters={
     *      {"name"="search", "dataType"="string"},
     *      {"name"="offset", "dataType"="integer"},
     *      {"name"="max_results", "dataType"="integer"}
     *  }
     * )
     */
    public function getAction()
    {
        $view = new View();
        return $this->get('fos_rest.view_handler')->handle(
            $view->setData(
                $this->get('oro_search.index')->simpleSearch(
                    $this->getRequest()->get('search'),
                    (int) $this->getRequest()->get('offset'),
                    (int) $this->getRequest()->get('max_results')
                )->toSearchResultData()
            )
        );
    }
}