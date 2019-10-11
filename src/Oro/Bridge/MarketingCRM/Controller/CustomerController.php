<?php

namespace Oro\Bridge\MarketingCRM\Controller;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/customer")
 */
class CustomerController extends Controller
{
    /**
     * @param Request $request
     * @return array
     *
     * @Route(
     *        "/widget/tracking-events",
     *        name="oro_magento_widget_tracking_events"
     * )
     * @AclAncestor("oro_magento_customer_view")
     * @Template
     */
    public function trackingEventsAction(Request $request)
    {
        $customerIds = $request->query->filter(
            'customerIds',
            [],
            false,
            FILTER_VALIDATE_INT,
            FILTER_REQUIRE_ARRAY
        );

        return ['customerIds' => $customerIds];
    }
}
