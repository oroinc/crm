<?php
namespace Oro\Bundle\SearchBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class SearchController extends Controller
{
    /**
     * @Route("advanced-search", name="oro_search_advanced")
     */
    public function ajaxAdvancedSearchAction()
    {
        return $this->getRequest()->isXmlHttpRequest()
            ? new JsonResponse($this->get('oro_search.index')->advancedSearch(
                    $this->getRequest()->get('query')
                )->toSearchResultData())
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
            'entities' => $this->get('oro_search.index')->getEntitiesLabels(),
            'searchString' => $this->getRequest()->get('searchString'),
            'fromString' => $this->getRequest()->get('fromString'),
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
        $searchManager = $this->get('oro_search.index');
        $searchString = $request->get('search');
        $from = $request->get('from');

        $data = $searchManager->simpleSearch(
            $searchString,
            null,
            (int) $this->getRequest()->get('limit'),
            $from,
            (int) $request->get('page')
        );

        if ($this->getRequest()->isXmlHttpRequest()) {
            return new JsonResponse($data->toSearchResultData());
        } else {
            return array(
                'searchResults' => $this->get('knp_paginator')->paginate(
                    $data,
                    $this->get('request')->query->get('page', 1),
                    $request->get('limit')
                ),
                'searchString' => $request->get('search'),
                'entities' => $searchManager->getEntitiesLabels(),
                'search' => $searchString,
                'from' => $from
            );
        }
    }
}
