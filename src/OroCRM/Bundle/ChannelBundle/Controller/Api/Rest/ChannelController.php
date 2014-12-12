<?php

namespace OroCRM\Bundle\ChannelBundle\Controller\Api\Rest;

use Doctrine\ORM\EntityNotFoundException;

use Symfony\Component\HttpFoundation\Response;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use FOS\RestBundle\Util\Codes;
use FOS\RestBundle\Controller\Annotations\NamePrefix;
use FOS\RestBundle\Controller\Annotations\RouteResource;

use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Exception\ForbiddenException;
use Oro\Bundle\SoapBundle\Controller\Api\Rest\RestController;

use OroCRM\Bundle\ChannelBundle\Event\ChannelDeleteEvent;
use OroCRM\Bundle\ChannelBundle\Event\ChannelBeforeDeleteEvent;

/**
 * @RouteResource("channel")
 * @NamePrefix("orocrm_api_")
 */
class ChannelController extends RestController
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
        try {
            $channel = $this->getManager()->find($id);

            $this->get('event_dispatcher')->dispatch(
                ChannelBeforeDeleteEvent::EVENT_NAME,
                new ChannelBeforeDeleteEvent($channel)
            );

            $this->getDeleteHandler()->handleDelete($id, $this->getManager());
            $this->get('event_dispatcher')->dispatch(ChannelDeleteEvent::EVENT_NAME, new ChannelDeleteEvent($channel));
        } catch (EntityNotFoundException $notFoundEx) {
            return $this->handleView($this->view(null, Codes::HTTP_NOT_FOUND));
        } catch (ForbiddenException $forbiddenEx) {
            return $this->handleView(
                $this->view(['reason' => $forbiddenEx->getReason()], Codes::HTTP_FORBIDDEN)
            );
        }

        return $this->handleView($this->view(null, Codes::HTTP_NO_CONTENT));
    }

    /**
     * {@inheritdoc}
     */
    public function getForm()
    {
        throw new \BadMethodCallException('FormHandler is not available.');
    }

    /**
     * {@inheritdoc}
     */
    public function getManager()
    {
        return $this->get('orocrm_channel.channel.manager');
    }

    /**
     * {@inheritdoc}
     */
    public function getFormHandler()
    {
        throw new \BadMethodCallException('FormHandler is not available.');
    }
}
