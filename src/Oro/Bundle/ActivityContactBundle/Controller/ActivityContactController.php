<?php

namespace Oro\Bundle\ActivityContactBundle\Controller;

use Oro\Bundle\ActivityContactBundle\Provider\EntityActivityContactDataProvider;
use Oro\Bundle\EntityBundle\Tools\EntityRoutingHelper;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Serves activity contact actions.
 */
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
        $entity       = $this->get(EntityRoutingHelper::class)->getEntity($entityClass, $entityId);
        $dataProvider = $this->get(EntityActivityContactDataProvider::class);
        $data         = $dataProvider->getEntityContactData($entity);

        return $data
            ? ['entity' => $entity, 'data' => $data]
            : new Response();
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedServices()
    {
        return array_merge(
            parent::getSubscribedServices(),
            [
                EntityRoutingHelper::class,
                EntityActivityContactDataProvider::class,
            ]
        );
    }
}
