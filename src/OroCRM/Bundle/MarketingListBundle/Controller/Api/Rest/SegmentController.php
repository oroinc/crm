<?php

namespace OroCRM\Bundle\MarketingListBundle\Controller\Api\Rest;

use Symfony\Component\HttpFoundation\Response;

use FOS\RestBundle\Util\Codes;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Routing\ClassResourceInterface;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\SoapBundle\Controller\Api\Rest\RestController;
use Oro\Bundle\SegmentBundle\Entity\Segment;

/**
 * @Rest\RouteResource("marketinglist_segment")
 * @Rest\NamePrefix("orocrm_api_")
 */
class SegmentController extends RestController implements ClassResourceInterface
{
    /**
     * @param int $id
     *
     * @ApiDoc(
     *      description="Run Static Marketing List Segment",
     *      resource=true
     * )
     * @AclAncestor("orocrm_marketing_list_update")
     * @return Response
     */
    public function postRunAction($id)
    {
        /** @var Segment $segment */
        $segment = $this->getManager()->find($id);
        if (!$segment) {
            return $this->handleView($this->view(null, Codes::HTTP_NOT_FOUND));
        }

        try {
            $this->get('oro_segment.static_segment_manager')->run($segment);
            return $this->handleView($this->view(null, Codes::HTTP_NO_CONTENT));
        } catch (\LogicException $e) {
            return $this->handleView($this->view(null, Codes::HTTP_BAD_REQUEST));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getManager()
    {
        return $this->get('oro_segment.segment_manager.api');
    }

    /**
     * {@inheritdoc}
     *
     * @throws \BadMethodCallException
     */
    public function getForm()
    {
        throw new \BadMethodCallException('This method is not implemented yet.');
    }

    /**
     * {@inheritdoc}
     *
     * @throws \BadMethodCallException
     */
    public function getFormHandler()
    {
        throw new \BadMethodCallException('This method is not implemented yet.');
    }
}
