<?php

namespace OroCRM\Bundle\ReportBundle\EventListener;

use Doctrine\ORM\EntityManager;
use Knp\Menu\ItemInterface;

use Knp\Menu\MenuItem;
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
            $reports = $this->em->getRepository('OroCRM\Bundle\ReportBundle\Entity\Report')->findBy([], ['name' => 'ASC']);
            //todo: Add ACL Access level protection
            if (!empty($reports)) {
                $reportMenuData = [];
                foreach ($reports as $report) {
                    $config = $this->entityConfigProvider->getConfig($report->getEntity());
                    $entityLabel = $config->get('plural_label');
                    $reportMenuData[$entityLabel] = [
                        'label' => $report->getName(),
                        'id' => $report->getId()
                    ];
                }
                ksort($reportMenuData);
                $this->buildReportMenu($reportsMenuItem, $reportMenuData);
            }
        }
    }

    /**
     * Build report menu
     *
     * @param ItemInterface $reportsItem
     * @param array $reportData
     *  key => entity label
     *  value => array of reports id's and label's
     */
    protected function buildReportMenu(ItemInterface $reportsItem, $reportData)
    {
        foreach ($reportData as $entityName => $report) {
            $this->getEntityMenuItem($reportsItem, $entityName)->addChild(
                $report['label'] . '_report',
                [
                    'label' => $report['label'] ,
                    'route' => 'orocrm_report_view',
                    'routeParameters' => [
                        'id' => $report['id']
                    ]
                ]
            );
        }
    }

    /**
     * Get entity menu item for report item
     *
     * @param ItemInterface $reportItem
     * @param string $entityName
     * @return ItemInterface
     */
    protected function getEntityMenuItem(ItemInterface $reportItem, $entityName)
    {
        $entityItemName = $entityName . '_report_tab';
        $entityItem = $reportItem->getChild($entityItemName);
        if (!$entityItem) {
            $reportItem->addChild(
                $entityItemName,
                [
                    'label' => $entityName,
                    'uri' => '#',
                ]
            );
            $entityItem = $reportItem->getChild($entityItemName);
        }

        return $entityItem;
    }
}
