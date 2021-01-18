<?php

namespace Oro\Bundle\ActivityContactBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ActivityContactController extends AbstractController
{
    /**
     * @param string  $entityClass The entity class which metrics should be rendered
     * @param integer $entityId    The entity object id which metrics should be rendered
     *
     * @return array|Response
     *
     * @Route(
     *      "/metrics/{entityClass}/{entityId}",
     *      name="oro_activity_contact_metrics"
     * )
     * @Template("@OroActivityContact/ActivityContact/widget/metrics.html.twig")
     */
    public function metricsAction($entityClass, $entityId)
    {
        $entity       = $this->get('oro_entity.routing_helper')->getEntity($entityClass, $entityId);
        $dataProvider = $this->get('oro_activity_contact.entity_activity_contact_data_provider');
        $data         = $dataProvider->getEntityContactData($entity);

        return $data
            ? ['entity' => $entity, 'data' => $data]
            : new Response();
    }
}
