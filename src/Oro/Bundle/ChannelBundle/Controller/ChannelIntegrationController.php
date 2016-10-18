<?php

namespace Oro\Bundle\ChannelBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\IntegrationBundle\Entity\Channel as Integration;

/**
 * @Route("/integration")
 */
class ChannelIntegrationController extends Controller
{
    /**
     * @Route("/create/{type}/{channelName}", requirements={"type"="\w+"}, name="oro_channel_integration_create")
     * @AclAncestor("oro_integration_create")
     * @Template("OroChannelBundle:ChannelIntegration:update.html.twig")
     */
    public function createAction($type, $channelName = null)
    {
        $translator      = $this->get('translator');
        $integrationName = urldecode($channelName) . ' ' . $translator->trans('oro.channel.data_source.label');
        $integration     = new Integration();
        $integration->setType(urldecode($type));
        $integration->setName(trim($integrationName));

        return $this->update($integration);
    }

    /**
     * @Route("/update/{id}", requirements={"id"="\d+"}, name="oro_channel_integration_update")
     * @AclAncestor("oro_integration_update")
     * @Template()
     */
    public function updateAction(Integration $integration)
    {
        return $this->update($integration);
    }

    /**
     * @param Integration $integration
     *
     * @return array
     */
    protected function update(Integration $integration)
    {
        $handler = $this->get('oro_channel.channel_integration_form.handler');

        $data = null;
        if ($handler->process($integration)) {
            $data = $handler->getFormSubmittedData();
        }

        return [
            'form'        => $handler->getFormView(),
            'isSubmitted' => null !== $data,
            'savedId'     => $data
        ];
    }
}
