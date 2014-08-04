<?php

namespace OroCRM\Bundle\ChannelBundle\Controller;

use OroCRM\Bundle\ChannelBundle\Event\ChannelChangeStatusEvent;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Oro\Bundle\SecurityBundle\Annotation\Acl;
use OroCRM\Bundle\ChannelBundle\Entity\Channel;

class ChannelController extends Controller
{
    /**
     * @Route("/", name="orocrm_channel_index")
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
        if ($this->get('orocrm_channel.channel_form.handler')->process($channel)) {
            $this->get('session')->getFlashBag()->add(
                'success',
                $this->get('translator')->trans('orocrm.channel.controller.message.saved')
            );

            return $this->get('oro_ui.router')->redirectAfterSave(
                ['route' => 'orocrm_channel_update', 'parameters' => ['id' => $channel->getId()]],
                ['route' => 'orocrm_channel_index'],
                $channel
            );
        }

        return [
            'entity' => $channel,
            'form'   => $this->get('orocrm_channel.form.channel')->createView(),
        ];
    }

    /**
     * @Route("/status/{status}/{id}",
     * requirements={"id"="\d+", "status"="active|deactive"}, name="orocrm_channel_change_status")
     * @Acl(
     *      id="orocrm_channel_update",
     *      type="entity",
     *      permission="EDIT",
     *      class="OroCRMChannelBundle:Channel"
     * )
     *
     */
    public function changeStatusAction(Channel $channel)
    {
        $newStatus  = $this->getRequest()->get('status');
        $event      = new ChannelChangeStatusEvent($channel);
        $dispatcher = new EventDispatcher();
        if ($newStatus == 'active') {
            $message = 'orocrm.channel.controller.message.status.activated';
            $channel->setStatus($channel::STATUS_ENABLED);
        } else {
            $message = 'orocrm.channel.controller.message.status.inactivated';
            $channel->setStatus($channel::STATUS_DISABLED);
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($channel);
        $em->flush();
        $dispatcher->dispatch($event::EVENT_NAME, $event);
        $this->get('session')->getFlashBag()->add(
            'success',
            $this->get('translator')->trans($message)
        );

        return new RedirectResponse($this->generateUrl('orocrm_channel_update', ['id' => $channel->getId()]));
    }
}
