<?php
namespace Oro\Bundle\SegmentationTreeBundle\Controller;

use Oro\Bundle\SegmentationTreeBundle\Model\AbstractSegment;
use Oro\Bundle\SegmentationTreeBundle\Helper\JsonSegmentHelper;

use Doctrine\ORM\EntityManager;
use Doctrine\Common\Collections\ArrayCollection;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;


/**
 * Base Segment controller
 *
 * @author Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
 */
abstract class BaseSegmentController extends Controller
{
    /**
     * Redirect to index action
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * TODO: try to make it automatic 
     */
    abstract protected function redirectToIndex();

    /**
     * Display index screen
     *
     * @return Response
     *
     * @Route("/index")
     * @Template()
     */
    public function indexAction()
    {
        return array();
    }

    /**
     * Send children segments linked to the one which id is provided
     *
     * @param Request $request
     *
     * @return Response
     *
     * @Method("GET")
     * @Route("/children")
     * @Template()
     *
     */
    public function childrenAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $parentId = $request->get('id');

            $segments = $this->getSegmentManager()->getChildren($parentId);

            $data = JsonSegmentHelper::childrenResponse($segments);

            return $this->prepareJsonResponse($data);
        } else {
            return $this->redirectToIndex();
        }
    }

    /**
     * Search for a segment by its title
     *
     * @param Request $request
     *
     * @return Response
     *
     * @Route("/search")
     * @Template()
     */
    public function searchAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $search = $request->get('search_str');

            $segments = $this->getSegmentManager()->search(array('title' => $search));

            $data = JsonSegmentHelper::searchResponse($segments);

            return $this->prepareJsonResponse($data);
        } else {
            return $this->redirectToIndex();
        }
    }

    /**
     * Create a new node
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @Method("POST")
     * @Route("/create-node")
     * @Template()
     */
    public function createNodeAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $parentId = $request->get('id');
            $title = $request->get('title');
            
            $segment = $this->getSegmentManager()->createSegment();

            $segment->setTitle($title);

            $repo = $this->getSegmentManager()->getEntityRepository();
            $parent = $repo->find($parentId);

            $segment->setParent($parent);

            $this->getSegmentManager()->getStorageManager()->persist($segment);
            $this->getSegmentManager()->getStorageManager()->flush();

            $data = JsonSegmentHelper::createNodeResponse(1, $segment->getId());

            return $this->prepareJsonResponse($data);
        } else {
            return $this->redirectToIndex();
        }
    }

    /**
     * Rename a node
     * @param Request $request
     *
     * @return Response
     *
     * @Method("POST")
     * @Route("/rename-node")
     * @Template()
     */
    public function renameNodeAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $this->getSegmentManager()->rename($request->get('id'), $request->get('title'));

            $data = JsonSegmentHelper::statusOKResponse();

            return $this->prepareJsonResponse($data);
        } else {
            return $this->redirectToIndex();
        }
    }

    /**
     * Remove a node
     * @param Request $request
     *
     * @return Response
     *
     * @Method("POST")
     * @Route("/remove-node")
     * @Template()
     */
    public function removeNodeAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $this->getSegmentManager()->removeFromId($request->get('id'));

            $data = JsonSegmentHelper::statusOKResponse();

            return $this->prepareJsonResponse($data);
        } else {
            return $this->redirectToIndex();
        }
    }

    /**
     * Move a node
     * @param Request $request
     *
     * @return Response
     *
     * @Method("POST")
     * @Route("/move-node")
     * @Template()
     */
    public function moveNodeAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $segmentId = $request->get('id');
            $referenceId = $request->get('ref');

            if ($request->get('copy') == 1) {
                $this->getSegmentManager()->copy($segmentId, $referenceId);
            } else {
                $this->getSegmentManager()->move($segmentId, $referenceId);
            }

            // format response to json content
            $data = JsonSegmentHelper::statusOKResponse();

            return $this->prepareJsonResponse($data);
        } else {
            return $this->redirectToIndex();
        }
    }

    /**
     * Return a response in json content type with well formated data
     * @param mixed $data
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function prepareJsonResponse($data)
    {
        $response = new Response(json_encode($data));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * Get the Segment manager associated with this controller
     * 
     * @return SegmentManager
     */
    abstract protected function getSegmentManager();


    /**
     * List items associated with the provided segment
     *
     * @param Request $request Request (segment_id)
     *
     * @return Response
     *
     * @Method("GET")
     * @Route("/list-items")
     * @Template()
     *
     */
    public function listItemsAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $segmentId = $request->get('segment_id');

            $repo = $this->getSegmentManager()->getEntityRepository();
            $segment = $repo->find($segmentId);

            $response = $this->prepareItemListResponse($segment);

            return $this->prepareJsonResponse($response);
        } else {
            return $this->redirectToIndex();
        }
    }
    
    /**
     * Fetch items list associated with this segment and prepare it
     * be send via Ajax.
     * The items must be returned as an array that will be
     * turned into a JSON representation via prepareJsonResponse
     *
     * @param AbstractSegment segment
     * @return Array items
     */
    abstract protected function prepareItemListResponse(AbstractSegment $segment);

    /**
     * Associate item to the specified segment
     *
     * @param Request $request Request (segment_id, item_id)
     *
     * @return Response
     *
     * @Method("POST")
     * @Route("/add-item")
     *
     * TODO: Manage multiple items addition for future grid use
     */
    abstract public function addItemAction(Request $request);

    /**
     * Remove association between the specified item and the specified segment
     *
     * @param Request $request Request (segment_id, item_id)
     *
     * @return Response
     *
     * @Method("POST")
     * @Route("/remove-item")
     *
     * TODO: Manage multiple items addition for future grid use
     */
    abstract public function removeItemAction(Request $request);

    
}
