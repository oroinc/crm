<?php

namespace OroCRM\Bundle\ChannelBundle\Controller\Api\Rest;

use Symfony\Component\HttpFoundation\Response;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use FOS\Rest\Util\Codes;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations\NamePrefix;
use FOS\RestBundle\Controller\Annotations\RouteResource;

use Oro\Bundle\SecurityBundle\Annotation\Acl;

/**
 * @RouteResource("channel")
 * @NamePrefix("orocrm_api_")
 */
class ChannelController extends FOSRestController
{
    /**
     * REST DELETE
     *
     * @param int $id
     *
     * @ApiDoc(
     *      description="Delete Channel",
     *      resource=true
     * )
     * @Acl(
     *      id="orocrm_channel_delete",
     *      type="entity",
     *      permission="DELETE",
     *      class="OroCRMChannelBundle:Channel"
     * )
     * @return Response
     */
    public function deleteAction($id)
    {
        $entity = $this->get('orocrm_channel.manager.api')->find($id);

        if (!$entity) {
            return $this->handleView($this->view(null, Codes::HTTP_NOT_FOUND));
        }

        if (!$this->get('orocrm_channel.channel.delete_manager')->delete($entity)) {
            return $this->handleView($this->view(null, Codes::HTTP_INTERNAL_SERVER_ERROR));
        }

        return $this->handleView($this->view(null, Codes::HTTP_NO_CONTENT));
    }
}
