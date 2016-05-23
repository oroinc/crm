<?php

namespace OroCRM\Bundle\ChannelBundle\Controller;

use Oro\Component\MessageQueue\Client\MessagePriority;
use Oro\Component\MessageQueue\Client\MessageProducer;
use OroCRM\Bundle\ChannelBundle\Async\Topics;
use OroCRM\Bundle\ChannelBundle\Entity\Channel;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;

class ChannelController extends Controller
{
    /**
     * @Route(
     *      "/{_format}",
     *      name="orocrm_channel_index",
     *      requirements={"_format"="html|json"},
     *      defaults={"_format"="html"}
     * )
     * @Acl(
     *      id="orocrm_channel_view",
     *      type="entity",
     *      permission="VIEW",
     *      class="OroCRMChannelBundle:Channel"
     * )
     * @Template()
     */
    public function indexAction()
    {
        return [];
    }

    /**
     * @Route("/create", name="orocrm_channel_create")
     * @Acl(
     *      id="orocrm_channel_create",
     *      type="entity",
     *      permission="CREATE",
     *      class="OroCRMChannelBundle:Channel"
     * )
     * @Template("OroCRMChannelBundle:Channel:update.html.twig")
     */
    public function createAction()
    {
        return $this->update(new Channel());
    }

    /**
     * @Route("/update/{id}", requirements={"id"="\d+"}, name="orocrm_channel_update")
     * @Acl(
     *      id="orocrm_channel_update",
     *      type="entity",
     *      permission="EDIT",
     *      class="OroCRMChannelBundle:Channel"
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
        $handler = $this->get('orocrm_channel.channel_form.handler');

        if ($handler->process($channel)) {
            $this->get('session')->getFlashBag()->add(
                'success',
                $this->get('translator')->trans('orocrm.channel.controller.message.saved')
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
     *      name="orocrm_channel_change_status"
     *  )
     * @AclAncestor("orocrm_channel_update")
     */
    public function changeStatusAction(Channel $channel)
    {
        if ($channel->getStatus() == Channel::STATUS_ACTIVE) {
            $message = 'orocrm.channel.controller.message.status.deactivated';
            $channel->setStatus(Channel::STATUS_INACTIVE);
        } else {
            $message = 'orocrm.channel.controller.message.status.activated';
            $channel->setStatus(Channel::STATUS_ACTIVE);
        }

        $this->getDoctrine()
            ->getManager()
            ->flush();

        $this->getMessageProducer()->send(
            Topics::CHANNEL_STATUS_CHANGED,
            ['channelId' => $channel->getId()],
            MessagePriority::HIGH
        );

        $this->get('session')->getFlashBag()->add('success', $this->get('translator')->trans($message));

        return $this->redirect(
            $this->generateUrl(
                'orocrm_channel_view',
                [
                    'id' => $channel->getId(),
                    '_enableContentProviders' => 'mainMenu'
                ]
            )
        );
    }

    /**
     * @Route("/view/{id}", requirements={"id"="\d+"}, name="orocrm_channel_view")
     * @AclAncestor("orocrm_channel_view")
     * @Template()
     */
    public function viewAction(Channel $channel)
    {
        return [
            'entity' => $channel,
        ];
    }

    /**
     * @Route("/widget/info/{id}", name="orocrm_channel_widget_info", requirements={"id"="\d+"})
     * @AclAncestor("orocrm_channel_view")
     * @Template()
     */
    public function infoAction(Channel $channel)
    {
        return [
            'channel' => $channel
        ];
    }

    /**
     * @return MessageProducer
     */
    protected function getMessageProducer()
    {
        return $this->get('oro_message_queue.message_producer');
    }
}
