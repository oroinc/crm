<?php

namespace OroCRM\Bundle\ReportBundle\EventListener;

use Doctrine\ORM\EntityManager;
use Knp\Menu\ItemInterface;

use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;
use Oro\Bundle\NavigationBundle\Event\ConfigureMenuEvent;

class NavigationListener
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var ConfigProvider
     */
    protected $entityConfigProvider = null;

    /**
     * @param EntityManager $entityManager
     * @param ConfigProvider $entityConfigProvider
     */
    public function __construct(
        EntityManager $entityManager,
        ConfigProvider $entityConfigProvider
    ) {
        $this->em = $entityManager;
        $this->entityConfigProvider = $entityConfigProvider;
    }

    /**
     * @param ConfigureMenuEvent $event
     */
    public function onNavigationConfigure(ConfigureMenuEvent $event)
    {
        /** @var ItemInterface $reportsMenuItem */
        $reportsMenuItem = $event->getMenu()->getChild('reports_tab');
        if ($reportsMenuItem) {
            $reports = $this->em->getRepository('OroCRM\Bundle\ReportBundle\Entity\Report')->findBy([], ['entity' => 'ASC', 'name' => 'ASC']);
            //todo: Add ACL Access level protection
            if (!empty($reports)) {
                foreach ($reports as $report) {
                    $this->getEntityMenuItem($reportsMenuItem, $report->getEntity())->addChild(
                        $report->getName(),
                        [
                            'label' => $report->getName(),
                            'route' => 'orocrm_report_view',
                            'routeParameters' => [
                                'id' => $report->getId()
                            ],
                            'extras' => [
                                'safe_label' => true,
                                'routes' => array('orocrm_report_*')
                            ]

                        ]
                    );
                }
            }
        }
    }

    /**
     * Get entity menu item for retprt item
     *
     * @param ItemInterface $reportItem
     * @param $entityClass
     * @return ItemInterface
     */
    protected function getEntityMenuItem(ItemInterface $reportItem, $entityClass)
    {
        $config = $this->entityConfigProvider->getConfig($entityClass);
        $entityLabel = $config->get('label');
        $entityItemName = $entityLabel . '_report_tab';
        $entityItem = $reportItem->getChild($entityItemName);
        if (!$entityItem) {
            $reportItem->addChild(
                $entityItemName,
                [
                    'label' => $entityLabel,
                    'uri' => '#',
                ]
            );
            $entityItem = $reportItem->getChild($entityItemName);
        }

        return $entityItem;
    }
}
