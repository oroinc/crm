<?php

namespace Oro\Bundle\SearchBundle\Controller\Api;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\View\View;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use FOS\RestBundle\Controller\Annotations\NamePrefix;

/**
 * @RouteResource("advanced_search")
 * @NamePrefix("oro_api_")
 */
class AdvancedSearchController extends FOSRestController
{
    /**
     * @ApiDoc(
     *  description="Get advanced search result",
     *  resource=true,
     *  filters={
     *      {"name"="query", "dataType"="string"},
     *      {"name"="from", "dataType"="string"}
     *  }
     * )
     */
    public function getAction()
    {
        $view = new View();

        return $this->get('fos_rest.view_handler')->handle(
            $view->setData(
                $this->get('oro_search.index')->advancedSearch(
                    $this->getRequest()->get('query'),
                    $this->getRequest()->get('from')
                )->toSearchResultData()
            )
        );
    }
}