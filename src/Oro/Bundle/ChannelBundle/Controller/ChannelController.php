<?php

namespace Oro\Bundle\ChannelBundle\Controller;

use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\ChannelBundle\Form\Handler\ChannelHandler;
use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\UIBundle\Route\Router;
use Oro\Component\MessageQueue\Client\MessageProducer;
use Oro\Component\MessageQueue\Client\MessageProducerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * CRUD controller for Channels.
 */
class ChannelController extends AbstractController
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
     * @Template("@OroChannel/Channel/update.html.twig")
     */
    public function createAction(Request $request)
    {
        return $this->update(new Channel(), $request);
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
    public function updateAction(Channel $channel, Request $request)
    {
        return $this->update($channel, $request);
    }

    /**
     * @param Channel $channel
     * @param Request $request
     *
     * @return array
     */
    protected function update(Channel $channel, Request $request)
    {
        $handler = $this->get(ChannelHandler::class);

        if ($handler->process($channel)) {
            $request->getSession()->getFlashBag()->add(
                'success',
                $this->get(TranslatorInterface::class)->trans('oro.channel.controller.message.saved')
            );

            return $this->get(Router::class)->redirect($channel);
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
        return $this->get(MessageProducerInterface::class);
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedServices()
    {
        return array_merge(
            parent::getSubscribedServices(),
            [
                TranslatorInterface::class,
                Router::class,
                MessageProducerInterface::class,
                ChannelHandler::class,
            ]
        );
    }
}
