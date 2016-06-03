<?php

namespace OroCRM\Bundle\SalesBundle\Migrations\Data\ORM;

use Doctrine\ORM\EntityManager;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

use Oro\Bundle\ReportBundle\Entity\Report;
use Oro\Bundle\ReportBundle\Entity\ReportType;

class LoadCampaignPerformanceReport extends AbstractFixture implements
    ContainerAwareInterface,
    DependentFixtureInterface
{
    /** @var ContainerInterface */
    protected $container;

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [
            'Oro\Bundle\ReportBundle\Migrations\Data\ORM\LoadReportTypes',
            'Oro\Bundle\OrganizationBundle\Migrations\Data\ORM\LoadOrganizationAndBusinessUnitData'
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * Load "Campaign Performance" report definition
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        /** @var EntityManager $em */
        $em = $this->container->get('doctrine.orm.default_entity_manager');

        $report = new Report();
        $report->setName('Campaign Performance');
        $report->setEntity('OroCRM\Bundle\CampaignBundle\Entity\Campaign');
        $type = $em->getReference('OroReportBundle:ReportType', ReportType::TYPE_TABLE);
        $report->setType($type);
        // @codingStandardsIgnoreStart
        $definition = [
            'filters'          => [],
            'columns'          => [
                ['name' => 'name', 'label' => 'Name', 'func' => '', 'sorting' => ''],
                ['name' => 'code', 'label' => 'Code', 'func' => '', 'sorting' => ''],
                [
                    'name'    => 'OroCRM\\Bundle\\SalesBundle\\Entity\\Lead::campaign+OroCRM\\Bundle\\SalesBundle\\Entity\\Lead::id',
                    'label'   => 'Leads',
                    'func'    => [
                        'name'       => 'Count',
                        'group_type' => 'aggregates',
                        'group_name' => 'number'
                    ],
                    'sorting' => ''
                ],
                [
                    'name'    => 'OroCRM\\Bundle\\SalesBundle\\Entity\\Lead::campaign+OroCRM\\Bundle\\SalesBundle\\Entity\\Lead::opportunities+OroCRM\\Bundle\\SalesBundle\\Entity\\Opportunity::id',
                    'label'   => 'Opportunities',
                    'func'    => [
                        'name'       => 'Count',
                        'group_type' => 'aggregates',
                        'group_name' => 'number'
                    ],
                    'sorting' => ''
                ],
                [
                    'name'    => 'OroCRM\\Bundle\\SalesBundle\\Entity\\Lead::campaign+OroCRM\\Bundle\\SalesBundle\\Entity\\Lead::opportunities+OroCRM\\Bundle\\SalesBundle\\Entity\\Opportunity::status',
                    'label'   => 'Number Won',
                    'func'    => [
                        'name'       => 'WonCount',
                        'group_type' => 'aggregates',
                        'group_name' => 'opportunity_status'
                    ],
                    'sorting' => ''
                ],
                [
                    'name'    => 'OroCRM\\Bundle\\SalesBundle\\Entity\\Lead::campaign+OroCRM\\Bundle\\SalesBundle\\Entity\\Lead::opportunities+OroCRM\\Bundle\\SalesBundle\\Entity\\Opportunity::status',
                    'label'   => 'Number Lost',
                    'func'    => [
                        'name'       => 'LostCount',
                        'group_type' => 'aggregates',
                        'group_name' => 'opportunity_status'
                    ],
                    'sorting' => ''
                ],
                [
                    'name'    => 'OroCRM\\Bundle\\SalesBundle\\Entity\\Lead::campaign+OroCRM\\Bundle\\SalesBundle\\Entity\\Lead::opportunities+OroCRM\\Bundle\\SalesBundle\\Entity\\Opportunity::closeRevenue',
                    'label'   => 'Close revenue',
                    'func'    => [
                        'name'       => 'WonRevenueSumFunction',
                        'group_type' => 'aggregates',
                        'group_name' => 'opportunity'
                    ],
                    'sorting' => 'DESC'
                ]
            ],
            'grouping_columns' => [
                [
                    'name' => 'code'
                ],
                [
                    'name' => 'name'
                ],
            ]
        ];
        // @codingStandardsIgnoreEnd
        $report->setDefinition(json_encode($definition));
        $report->setOrganization($manager->getRepository('OroOrganizationBundle:Organization')->getFirst());
        $report->setOwner($manager->getRepository('OroOrganizationBundle:BusinessUnit')->getFirst());
        $em->persist($report);
        $em->flush($report);
    }
}
