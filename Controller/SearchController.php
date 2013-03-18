<?php
namespace Oro\Bundle\SearchBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class SearchController extends Controller
{

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
     * @Route("results", name="oro_search_results", defaults={"limit"=10})
     * @Template
     */
    public function searchResultsAction()
    {
        $request = $this->getRequest();
        $searchManager = $this->get('oro_search.index');
        $searchString = $request->get('search');
        $from = $request->get('from');

        return array(
            'searchResults' => $this->get('knp_paginator')->paginate(
                $searchManager->simpleSearch(
                    $searchString,
                    null,
                    (int) $request->get('limit'),
                    $from,
                    (int) $request->get('page')
                ),
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
