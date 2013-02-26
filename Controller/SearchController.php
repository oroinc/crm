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
        );
    }

    /**
     * Show search results
     *
     * @Route("results", name="oro_search_results")
     * @Template
     */
    public function searchResultsAction()
    {
        $request = $this->getRequest();
        $searchManager = $this->get('oro_search.index');
        $searchString = $request->get('search');
        $from = $request->get('from');

        return array(
            'searchResults' => $searchManager->simpleSearch(
                $searchString,
                (int) $request->get('offset'),
                (int) $request->get('max_results'),
                $from
            ),
            'searchString' => $this->getRequest()->get('search'),
            'entities' => $searchManager->getEntitiesLabels(),
            'search' => $searchString,
            'from' => $from
        );
    }
}
