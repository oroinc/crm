<?php

namespace OroCRM\Bundle\ReportBundle\EventListener;

use Doctrine\ORM\EntityManager;

use Oro\Bundle\NavigationBundle\Event\ConfigureMenuEvent;

class NavigationListener
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @param EntityManager  $entityManager
     */
    public function __construct(
        EntityManager $entityManager
    ) {
        $this->em = $entityManager;
    }

    /**
     * @param ConfigureMenuEvent $event
     */
    public function onNavigationConfigure(ConfigureMenuEvent $event)
    {
        $reportsMenuItem = $event->getMenu()->getChild('reports_tab');
        if ($reportsMenuItem) {
            $reports = $this->em->getRepository('OroCRM\Bundle\ReportBundle\Entity\Report')
                ->getReportsQB()
                ->getQuery()->getResult();
            //todo: Add ACL Access level protection
            if (!empty($reports)) {
                foreach ($reports as $report) {
                    $reportsMenuItem->addChild(
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
}
