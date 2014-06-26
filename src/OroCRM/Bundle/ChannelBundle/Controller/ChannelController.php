<?php

namespace OroCRM\Bundle\ChannelBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormInterface;

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
     * @Template
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
     *      permission="VIEW",
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
        $this->get('orocrm_channel.channel_form.handler')->process($channel);

        $form = $this->getForm();

        return [
            'entity' => $channel,
            'form'   => $form->createView(),
        ];

    }

    /**
     * Returns form instance
     *
     * @return FormInterface
     */
    protected function getForm()
    {
        return $this->get('orocrm_channel.form.channel');
    }
}
