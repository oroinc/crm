<?php
namespace Oro\Bundle\SearchBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

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
     * @Route(
     *      "/{_format}",
     *      name="oro_search_results",
     *      requirements={"_format"="html|json"},
     *      defaults={"_format"="html", "limit"=10}
     * )
     * @Template
     */
    public function searchResultsAction(Request $request)
    {
        $from   = $request->get('from');
        $search = $request->get('search');

        $view = 'json' == $request->getRequestFormat()
            ? 'OroGridBundle:Datagrid:list.json.php'
            : 'OroSearchBundle:Search:searchResults.html.twig';

        /** @var $datagrid AllResultsDatagrid */
        $datagrid = $this->get('oro_search.datagrid.all_results');

        $datagrid->setSearchEntity($from);
        $datagrid->setSearchString($search);

        $datagrid->getRouteGenerator()->setRouteParameters(
            array(
                'search' => $search,
                'from'   => $from
            )
        );

        return $this->render(
            $view,
            array(
                'searchString'  => $search,
                'entities'      => $this->get('oro_search.index')->getEntitiesLabels(),
                'from'          => $from,
                'datagrid'      => $datagrid->createView()
            )
        );
    }
}
