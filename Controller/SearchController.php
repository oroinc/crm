<?php
namespace Oro\Bundle\SearchBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

use Oro\Bundle\SearchBundle\Datagrid\SearchDatagridManager;

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

        /** @var $datagridManager SearchDatagridManager */
        $datagridManager = $this->get('oro_search.datagrid_results.datagrid_manager');

        $datagridManager->setSearchEntity($from);
        $datagridManager->setSearchString($search);
        $datagridManager->getRouteGenerator()->setRouteParameters(
            array(
                'from'   => $from,
                'search' => $search,
            )
        );

        $view = 'json' == $request->getRequestFormat()
            ? 'OroGridBundle:Datagrid:list.json.php'
            : 'OroSearchBundle:Search:searchResults.html.twig';

        return $this->render(
            $view,
            array(
                'from'         => $from,
                'entities'     => $this->get('oro_search.index')->getEntitiesLabels(),
                'searchString' => $search,
                'datagrid'     => $datagridManager->getDatagrid()->createView()
            )
        );
    }
}
