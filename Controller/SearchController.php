<?php
namespace Oro\Bundle\SearchBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

use Oro\Bundle\SearchBundle\Engine\Indexer;
use Oro\Bundle\SearchBundle\Datagrid\AllResultsDatagrid;

class SearchController extends Controller
{
    /**
     * @Route("advanced-search", name="oro_search_advanced")
     */
    public function ajaxAdvancedSearchAction()
    {
        return $this->getRequest()->isXmlHttpRequest()
            ? new JsonResponse(
                $this->get('oro_search.index')->advancedSearch(
                    $this->getRequest()->get('query')
                )->toSearchResultData()
            )
            : $this->forward('OroSearchBundle:Search:searchResults');
    }

    /**
     * Show search block
     *
     * @Template
     */
    public function searchBarAction()
    {
        return array(
            'entities'     => $this->get('oro_search.index')->getEntitiesLabels(),
            'searchString' => $this->getRequest()->get('searchString'),
            'fromString'   => $this->getRequest()->get('fromString'),
        );
    }

    /**
     * Show search results
     *
     * @Route("/", name="oro_search_results", defaults={"limit"=10})
     * @Template
     */
    public function searchResultsAction()
    {
        $request = $this->getRequest();

        /** @var $indexer Indexer */
        $indexer = $this->get('oro_search.index');
        $searchString = $request->get('search');
        $from = $request->get('from');

        $data = $indexer->simpleSearch(
            $searchString,
            null,
            (int)$this->getRequest()->get('limit'),
            $from,
            (int)$request->get('page')
        );

        /** @var $datagrid AllResultsDatagrid */
        $datagrid = $this->get('oro_search.datagrid.all_results');
        $datagrid->setSearchString($searchString);
        $results = $datagrid->getResults();

        if ($this->getRequest()->isXmlHttpRequest()) {
            return new JsonResponse($data->toSearchResultData());
        } else {
            return array(
                'searchResults' => $this->get('knp_paginator')->paginate(
                    $data,
                    $this->get('request')->query->get('page', 1),
                    $request->get('limit')
                ),
                'searchString'  => $request->get('search'),
                'entities'      => $indexer->getEntitiesLabels(),
                'search'        => $searchString,
                'from'          => $from
            );
        }
    }
}
