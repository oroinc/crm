<?php

namespace Oro\Bundle\ChannelBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\ChannelBundle\Event\ChannelChangeStatusEvent;

class ChannelController extends Controller
{
    /**
     * @Route(
     *      "/{_format}",
     *      name="oro_channel_index",
     *      requirements={"_format"="html|json"},
     *      defaults={"_format"="html"}
     * )
     * @Acl(
     *      id="oro_channel_view",
     *      type="entity",
     *      permission="VIEW",
     *      class="OroChannelBundle:Channel"
     * )
     * @Template()
     */
    public function indexAction()
    {
        return [];
    }

    /**
     * @Route("/create", name="oro_channel_create")
     * @Acl(
     *      id="oro_channel_create",
     *      type="entity",
     *      permission="CREATE",
     *      class="OroChannelBundle:Channel"
     * )
     * @Template("OroChannelBundle:Channel:update.html.twig")
     */
    public function createAction()
    {
        return $this->update(new Channel());
    }

    /**
     * @Route("/update/{id}", requirements={"id"="\d+"}, name="oro_channel_update")
     * @Acl(
     *      id="oro_channel_update",
     *      type="entity",
     *      permission="EDIT",
     *      class="OroChannelBundle:Channel"
     * )
     * @Template()
     */
    public function updateAction(Channel $channel)
    {
        return $this->update($channel);
    }

    /**
     * @param Channel $channel
     *
     * @return array
     */
    protected function update(Channel $channel)
    {
        $handler = $this->get('oro_channel.channel_form.handler');

        if ($handler->process($channel)) {
            $this->get('session')->getFlashBag()->add(
                'success',
                $this->get('translator')->trans('oro.channel.controller.message.saved')
            );

            return $this->get('oro_ui.router')->redirect($channel);
        }

        return [
            'entity' => $channel,
            'form'   => $handler->getFormView(),
        ];
    }

    /**
     * @Route(
     *      "/status/change/{id}",
     *      requirements={"id"="\d+"},
     *      name="oro_channel_change_status"
     *  )
     * @AclAncestor("oro_channel_update")
     */
    public function changeStatusAction(Channel $channel)
    {
        if ($channel->getStatus() == Channel::STATUS_ACTIVE) {
            $message = 'oro.channel.controller.message.status.deactivated';
            $channel->setStatus(Channel::STATUS_INACTIVE);
        } else {
            $message = 'oro.channel.controller.message.status.activated';
            $channel->setStatus(Channel::STATUS_ACTIVE);
        }

        $this->getDoctrine()
            ->getManager()
            ->flush();

        $event = new ChannelChangeStatusEvent($channel);

        $this->get('event_dispatcher')->dispatch(ChannelChangeStatusEvent::EVENT_NAME, $event);
        $this->get('session')->getFlashBag()->add('success', $this->get('translator')->trans($message));

        return $this->redirect(
            $this->generateUrl(
                'oro_channel_view',
                [
                    'id' => $channel->getId(),
                    '_enableContentProviders' => 'mainMenu'
                ]
            )
        );
    }

    /**
     * @Route("/view/{id}", requirements={"id"="\d+"}, name="oro_channel_view")
     * @AclAncestor("oro_channel_view")
     * @Template()
     */
    public function viewAction(Channel $channel)
    {
        return [
            'entity' => $channel,
        ];
    }

    /**
     * @Route("/widget/info/{id}", name="oro_channel_widget_info", requirements={"id"="\d+"})
     * @AclAncestor("oro_channel_view")
     * @Template()
     */
    public function infoAction(Channel $channel)
    {
        return [
            'channel' => $channel
        ];
    }
}
