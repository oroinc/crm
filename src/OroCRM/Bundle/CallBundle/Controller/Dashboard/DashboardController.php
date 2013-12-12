<?php

namespace OroCRM\Bundle\CallBundle\Controller\Dashboard;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class DashboardController extends Controller
{
    /**
     * @Route(
     *      "/recent_calls/{_format}",
     *      name="orocrm_call_dashboard_recentcalls",
     *      requirements={"_format"="html|json"},
     *      defaults={"_format" = "html"}
     * )
     * @Template("OroCRMCallBundle:Dashboard:recentCalls.html.twig")
     */
    public function recentCallsAction()
    {
        return [];
    }
}
