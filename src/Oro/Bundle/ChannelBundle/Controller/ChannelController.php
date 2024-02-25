<?php

namespace Oro\Bundle\ChannelBundle\Controller;

use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\ChannelBundle\Form\Handler\ChannelHandler;
use Oro\Bundle\SecurityBundle\Attribute\Acl;
use Oro\Bundle\SecurityBundle\Attribute\AclAncestor;
use Oro\Bundle\UIBundle\Route\Router;
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
    #[Route(
        path: '/{_format}',
        name: 'oro_channel_index',
        requirements: ['_format' => 'html|json'],
        defaults: ['_format' => 'html']
    )]
    #[Template]
    #[Acl(id: 'oro_channel_view', type: 'entity', class: Channel::class, permission: 'VIEW')]
    public function indexAction()
    {
        return [];
    }

    #[Route(path: '/create', name: 'oro_channel_create')]
    #[Template('@OroChannel/Channel/update.html.twig')]
    #[Acl(id: 'oro_channel_create', type: 'entity', class: Channel::class, permission: 'CREATE')]
    public function createAction(Request $request)
    {
        return $this->update(new Channel(), $request);
    }

    #[Route(path: '/update/{id}', name: 'oro_channel_update', requirements: ['id' => '\d+'])]
    #[Template]
    #[Acl(id: 'oro_channel_update', type: 'entity', class: Channel::class, permission: 'EDIT')]
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
        $handler = $this->container->get(ChannelHandler::class);

        if ($handler->process($channel)) {
            $request->getSession()->getFlashBag()->add(
                'success',
                $this->container->get(TranslatorInterface::class)->trans('oro.channel.controller.message.saved')
            );

            return $this->container->get(Router::class)->redirect($channel);
        }

        return [
            'entity' => $channel,
            'form'   => $handler->getFormView(),
        ];
    }

    #[Route(path: '/view/{id}', name: 'oro_channel_view', requirements: ['id' => '\d+'])]
    #[Template]
    #[AclAncestor('oro_channel_view')]
    public function viewAction(Channel $channel)
    {
        return [
            'entity' => $channel,
        ];
    }

    #[Route(path: '/widget/info/{id}', name: 'oro_channel_widget_info', requirements: ['id' => '\d+'])]
    #[Template]
    #[AclAncestor('oro_channel_view')]
    public function infoAction(Channel $channel)
    {
        return [
            'channel' => $channel
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedServices(): array
    {
        return array_merge(
            parent::getSubscribedServices(),
            [
                TranslatorInterface::class,
                Router::class,
                ChannelHandler::class,
            ]
        );
    }
}
