<?php

namespace OroCRM\Bundle\ActivityContactBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class ActivityContactController extends Controller
{
    /**
     * @param object $entity The entity object which contact metrics should be rendered
     *
     * @return Response
     *
     * @Route(
     *      "/metrics/{entity}",
     *      name="orocrm_activity_contact_metrics"
     * )
     * @Template
     */
    public function metricsAction($entity)
    {
        $widgetProvider = $this->get('oro_activity_list.widget_provider.before');

        $items = $widgetProvider->supports($entity)
            ? $widgetProvider->getWidgets($entity)
            : [];

        return ['entity' => $entity, 'items' => $items];
    }
}
