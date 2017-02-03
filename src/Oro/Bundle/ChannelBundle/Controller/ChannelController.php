<?php

namespace Oro\Bundle\ChannelBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Component\MessageQueue\Client\Message;
use Oro\Component\MessageQueue\Client\MessagePriority;
use Oro\Component\MessageQueue\Client\MessageProducer;
use Oro\Bundle\ChannelBundle\Async\Topics;
use Oro\Bundle\ChannelBundle\Entity\Channel;

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

    /**
     * @return MessageProducer
     */
    protected function getMessageProducer()
    {
        return $this->get('oro_message_queue.message_producer');
    }
}
