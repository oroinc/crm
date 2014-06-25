<?php

namespace OroCRM\Bundle\ChannelBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Oro\Bundle\SecurityBundle\Annotation\Acl;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;

use OroCRM\Bundle\ChannelBundle\Entity\Channel;

class ChannelController extends Controller
{
    /**
     * @Route("/", name="orocrm_channel_index")
     * @AclAncestor("orocrm_channel_view")
     * @Template
     */
    public function indexAction()
    {
        return [];
    }

    /**
     * @Route("/view/{id}", name="orocrm_channel_view", requirements={"id"="\d+"}))
     * @Acl(
     *      id="orocrm_channel_view",
     *      type="entity",
     *      permission="VIEW",
     *      class="OroCRMChannelBundle:Channel"
     * )
     * @Template
     */
    public function viewAction(Channel $channel)
    {
        return ['entity' => $channel];
    }
}
