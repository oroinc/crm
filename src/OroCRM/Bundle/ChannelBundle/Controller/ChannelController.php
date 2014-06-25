<?php

namespace OroCRM\Bundle\ChannelBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class ChannelController extends Controller
{
    /**
     * @Route("/", name="orocrm_channel_index")
     * @Template
     */
    public function indexAction()
    {
        return [];
    }
}
