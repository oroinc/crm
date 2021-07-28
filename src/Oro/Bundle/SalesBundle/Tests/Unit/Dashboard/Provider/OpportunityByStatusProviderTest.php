<?php

namespace Oro\Bundle\SalesBundle\Tests\Unit\Dashboard\Provider;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\CurrencyBundle\Query\CurrencyQueryBuilderTransformerInterface;
use Oro\Bundle\DashboardBundle\Filter\DateFilterProcessor;
use Oro\Bundle\DashboardBundle\Filter\WidgetProviderFilterManager;
use Oro\Bundle\DashboardBundle\Model\WidgetOptionBag;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\SalesBundle\Dashboard\Provider\OpportunityByStatusProvider;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;

class OpportunityByStatusProviderTest extends \PHPUnit\Framework\TestCase
{
    /** @var ManagerRegistry|\PHPUnit\Framework\MockObject\MockObject */
    protected $registry;

    /** @var AclHelper|\PHPUnit\Framework\MockObject\MockObject */
    protected $aclHelper;

    /** @var WidgetProviderFilterManager|\PHPUnit\Framework\MockObject\MockObject */
    protected $widgetProviderFilter;

    /** @var DateFilterProcessor|\PHPUnit\Framework\MockObject\MockObject */
    protected $dateFilterProcessor;

    /** @var  CurrencyQueryBuilderTransformerInterface|\PHPUnit\Framework\MockObject\MockObject */
    protected $qbTransformer;

    /** @var array */
    protected $opportunityStatuses = [
        ['id' => 'won', 'name' => 'Won'],
        ['id' => 'identification_alignment', 'name' => 'Identification'],
        ['id' => 'in_progress', 'name' => 'Open'],
        ['id' => 'needs_analysis', 'name' => 'Analysis'],
        ['id' => 'negotiation', 'name' => 'Negotiation'],
        ['id' => 'solution_development', 'name' => 'Development'],
        ['id' => 'lost', 'name' => 'Lost']
    ];

    /** @var  OpportunityByStatusProvider */
    protected $provider;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->registry = $this->getMockBuilder('Doctrine\Bundle\DoctrineBundle\Registry')
            ->disableOriginalConstructor()
            ->getMock();
        $this->aclHelper = $this->getMockBuilder('Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper')
            ->disableOriginalConstructor()
            ->getMock();
        $this->widgetProviderFilter = $this
            ->getMockBuilder('Oro\Bundle\DashboardBundle\Filter\WidgetProviderFilterManager')
            ->disableOriginalConstructor()
            ->getMock();
        $this->dateFilterProcessor = $this->getMockBuilder('Oro\Bundle\DashboardBundle\Filter\DateFilterProcessor')
            ->disableOriginalConstructor()
            ->getMock();
        $this->qbTransformer = $this->getMockForAbstractClass(
            'Oro\Bundle\CurrencyBundle\Query\CurrencyQueryBuilderTransformerInterface'
        );

        $this->provider = new OpportunityByStatusProvider(
            $this->registry,
            $this->aclHelper,
            $this->widgetProviderFilter,
            $this->dateFilterProcessor,
            $this->qbTransformer
        );
    }

    /**
     * @param WidgetOptionBag $widgetOptions
     * @param string          $expectation
     *
     * @dataProvider getOpportunitiesGroupedByStatusDQLDataProvider
     */
    public function testGetOpportunitiesGroupedByStatusDQL($widgetOptions, $expectation)
    {
        $opportunityQB = new QueryBuilder($this->getMockForAbstractClass('Doctrine\ORM\EntityManagerInterface'));
        $opportunityQB
            ->from('Oro\Bundle\SalesBundle\Entity\Opportunity', 'o', null);

        $statusesQB = $this->getMockQueryBuilder();
        $statusesQB->expects($this->once())
            ->method('select')
            ->with('s.id, s.name')
            ->willReturnSelf();
        $statusesQB->expects($this->once())
            ->method('getQuery')
            ->willReturnSelf();
        $statusesQB->expects($this->once())
            ->method('getArrayResult')
            ->willReturn($this->opportunityStatuses);

        $repository = $this->getMockRepository();
        $repository->expects($this->exactly(2))
            ->method('createQueryBuilder')
            ->withConsecutive(['o'], ['s'])
            ->willReturnOnConsecutiveCalls($opportunityQB, $statusesQB);

        $this->registry->expects($this->exactly(2))
            ->method('getRepository')
            ->withConsecutive(
                ['OroSalesBundle:Opportunity'],
                [ExtendHelper::buildEnumValueClassName('opportunity_status')]
            )
            ->willReturn($repository);

        $mockResult = $this->getMockQueryBuilder();
        $mockResult->expects($this->once())
            ->method('getArrayResult')
            ->willReturn([]);

        $self = $this;
        $this->aclHelper->expects($this->once())
            ->method('apply')
            ->with(
                $this->callback(function ($query) use ($self, $expectation) {
                    /** @var Query $query */
                    $self->assertEquals($expectation, $query->getDQL());

                    return true;
                })
            )
            ->willReturn($mockResult);

        $this->provider->getOpportunitiesGroupedByStatus($widgetOptions);
    }

    public function getOpportunitiesGroupedByStatusDQLDataProvider()
    {
        return [
            'request quantities'                                                    => [
                'widgetOptions' => new WidgetOptionBag([
                    'excluded_statuses' => [],
                    'useQuantityAsData' => true
                ]),
                'expected DQL'  =>
                    'SELECT IDENTITY (o.status) status, COUNT(o.id) as quantity '
                    . 'FROM Oro\Bundle\SalesBundle\Entity\Opportunity o '
                    . 'GROUP BY status '
                    . 'ORDER BY quantity DESC'
            ],
            'request quantities with excluded statuses - should not affect DQL'     => [
                'widgetOptions' => new WidgetOptionBag([
                    'excluded_statuses' => ['in_progress', 'won'],
                    'useQuantityAsData' => true
                ]),
                'expected DQL'  =>
                    'SELECT IDENTITY (o.status) status, COUNT(o.id) as quantity '
                    . 'FROM Oro\Bundle\SalesBundle\Entity\Opportunity o '
                    . 'GROUP BY status '
                    . 'ORDER BY quantity DESC'
            ],
            'request budget amounts'                                                => [
                'widgetOptions' => new WidgetOptionBag([
                    'excluded_statuses' => [],
                    'useQuantityAsData' => false
                ]),
                'expected DQL'  => <<<DQL
SELECT IDENTITY (o.status) status, SUM(
                        CASE WHEN o.status = 'won'
                            THEN (CASE WHEN o.closeRevenueValue IS NOT NULL THEN () ELSE 0 END)
                            ELSE (CASE WHEN o.budgetAmountValue IS NOT NULL THEN () ELSE 0 END)
                        END
                    ) as budget FROM Oro\Bundle\SalesBundle\Entity\Opportunity o GROUP BY status ORDER BY budget DESC
DQL
            ],
            'request budget amounts with excluded statuses - should not affect DQL' => [
                'widgetOptions' => new WidgetOptionBag([
                    'excluded_statuses' => ['in_progress', 'won'],
                    'useQuantityAsData' => false
                ]),
                'expected DQL'  => <<<DQL
SELECT IDENTITY (o.status) status, SUM(
                        CASE WHEN o.status = 'won'
                            THEN (CASE WHEN o.closeRevenueValue IS NOT NULL THEN () ELSE 0 END)
                            ELSE (CASE WHEN o.budgetAmountValue IS NOT NULL THEN () ELSE 0 END)
                        END
                    ) as budget FROM Oro\Bundle\SalesBundle\Entity\Opportunity o GROUP BY status ORDER BY budget DESC
DQL
            ]
        ];
    }

    /**
     * @param WidgetOptionBag $widgetOptions
     * @param array           $result
     * @param string          $expected
     *
     * @dataProvider getOpportunitiesGroupedByStatusResultDataProvider
     */
    public function testGetOpportunitiesGroupedByStatusResultFormatter($widgetOptions, $result, $expected)
    {
        $opportunityQB = new QueryBuilder($this->createMock('Doctrine\ORM\EntityManagerInterface'));
        $opportunityQB
            ->from('Oro\Bundle\SalesBundle\Entity\Opportunity', 'o', null);

        $statusesQB = $this->getMockQueryBuilder();
        $statusesQB->expects($this->once())
            ->method('select')
            ->with('s.id, s.name')
            ->willReturnSelf();
        $statusesQB->expects($this->once())
            ->method('getQuery')
            ->willReturnSelf();
        $statusesQB->expects($this->once())
            ->method('getArrayResult')
            ->willReturn($this->opportunityStatuses);

        $repository = $this->getMockRepository();
        $repository->expects($this->exactly(2))
            ->method('createQueryBuilder')
            ->withConsecutive(['o'], ['s'])
            ->willReturnOnConsecutiveCalls($opportunityQB, $statusesQB);

        $this->registry->expects($this->exactly(2))
            ->method('getRepository')
            ->withConsecutive(
                ['OroSalesBundle:Opportunity'],
                [ExtendHelper::buildEnumValueClassName('opportunity_status')]
            )
            ->willReturn($repository);

        $mockResult = $this->getMockQueryBuilder();
        $mockResult->expects($this->once())
            ->method('getArrayResult')
            ->willReturn($result);

        $this->aclHelper->expects($this->once())
            ->method('apply')
            ->willReturn($mockResult);

        $data = $this->provider->getOpportunitiesGroupedByStatus($widgetOptions);

        $this->assertEquals($expected, $data);
    }

    public function getOpportunitiesGroupedByStatusResultDataProvider()
    {
        return [
            'result with all statuses, no exclusions - only labels should be added'               => [
                'widgetOptions'             => new WidgetOptionBag([
                    'excluded_statuses' => [],
                    'useQuantityAsData' => true
                ]),
                'result data'               => [
                    0 => ['quantity' => 700, 'status' => 'won'],
                    1 => ['quantity' => 600, 'status' => 'identification_alignment'],
                    2 => ['quantity' => 500, 'status' => 'in_progress'],
                    3 => ['quantity' => 400, 'status' => 'needs_analysis'],
                    4 => ['quantity' => 300, 'status' => 'negotiation'],
                    5 => ['quantity' => 200, 'status' => 'solution_development'],
                    6 => ['quantity' => 100, 'status' => 'lost'],
                ],
                'expected formatted result' => [
                    0 => ['quantity' => 700, 'status' => 'won', 'label' => 'Won'],
                    1 => ['quantity' => 600, 'status' => 'identification_alignment', 'label' => 'Identification'],
                    2 => ['quantity' => 500, 'status' => 'in_progress', 'label' => 'Open'],
                    3 => ['quantity' => 400, 'status' => 'needs_analysis', 'label' => 'Analysis'],
                    4 => ['quantity' => 300, 'status' => 'negotiation', 'label' => 'Negotiation'],
                    5 => ['quantity' => 200, 'status' => 'solution_development', 'label' => 'Development'],
                    6 => ['quantity' => 100, 'status' => 'lost', 'label' => 'Lost'],
                ]
            ],
            'result with all statuses, with exclusions - excluded should be removed, labels'      => [
                'widgetOptions'             => new WidgetOptionBag([
                    'excluded_statuses' => ['identification_alignment', 'solution_development'],
                    'useQuantityAsData' => true
                ]),
                'result data'               => [
                    0 => ['quantity' => 700, 'status' => 'won'],
                    1 => ['quantity' => 600, 'status' => 'identification_alignment'],
                    2 => ['quantity' => 500, 'status' => 'in_progress'],
                    3 => ['quantity' => 400, 'status' => 'needs_analysis'],
                    4 => ['quantity' => 300, 'status' => 'negotiation'],
                    5 => ['quantity' => 200, 'status' => 'solution_development'],
                    6 => ['quantity' => 100, 'status' => 'lost'],
                ],
                'expected formatted result' => [
                    0 => ['quantity' => 700, 'status' => 'won', 'label' => 'Won'],
                    2 => ['quantity' => 500, 'status' => 'in_progress', 'label' => 'Open'],
                    3 => ['quantity' => 400, 'status' => 'needs_analysis', 'label' => 'Analysis'],
                    4 => ['quantity' => 300, 'status' => 'negotiation', 'label' => 'Negotiation'],
                    6 => ['quantity' => 100, 'status' => 'lost', 'label' => 'Lost'],
                ]
            ],
            'result with NOT all statuses, no exclusions - all statuses, labels'                  => [
                'widgetOptions'             => new WidgetOptionBag([
                    'excluded_statuses' => [],
                    'useQuantityAsData' => true
                ]),
                'result data'               => [
                    0 => ['quantity' => 700, 'status' => 'won'],
                    1 => ['quantity' => 300, 'status' => 'negotiation'],
                ],
                'expected formatted result' => [
                    0 => ['quantity' => 700, 'status' => 'won', 'label' => 'Won'],
                    1 => ['quantity' => 300, 'status' => 'negotiation', 'label' => 'Negotiation'],
                    2 => ['quantity' => 0, 'status' => 'identification_alignment', 'label' => 'Identification'],
                    3 => ['quantity' => 0, 'status' => 'in_progress', 'label' => 'Open'],
                    4 => ['quantity' => 0, 'status' => 'needs_analysis', 'label' => 'Analysis'],
                    5 => ['quantity' => 0, 'status' => 'solution_development', 'label' => 'Development'],
                    6 => ['quantity' => 0, 'status' => 'lost', 'label' => 'Lost'],
                ]
            ],
            'result with NOT all statuses AND exclusions - all statuses(except excluded), labels' => [
                'widgetOptions'             => new WidgetOptionBag([
                    'excluded_statuses' => ['identification_alignment', 'lost', 'in_progress'],
                    'useQuantityAsData' => true
                ]),
                'result data'               => [
                    0 => ['quantity' => 700, 'status' => 'won'],
                    1 => ['quantity' => 500, 'status' => 'in_progress'],
                    2 => ['quantity' => 300, 'status' => 'negotiation'],
                    3 => ['quantity' => 100, 'status' => 'lost'],
                ],
                'expected formatted result' => [
                    0 => ['quantity' => 700, 'status' => 'won', 'label' => 'Won'],
                    2 => ['quantity' => 300, 'status' => 'negotiation', 'label' => 'Negotiation'],
                    4 => ['quantity' => 0, 'status' => 'needs_analysis', 'label' => 'Analysis'],
                    5 => ['quantity' => 0, 'status' => 'solution_development', 'label' => 'Development'],
                ]
            ],
        ];
    }

    /**
     * @return EntityRepository|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function getMockRepository()
    {
        return $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->setMethods(['createQueryBuilder'])
            ->getMockForAbstractClass();
    }

    /**
     * @return QueryBuilder|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function getMockQueryBuilder()
    {
        return $this->getMockBuilder('Doctrine\ORM\QueryBuilder')
            ->disableOriginalConstructor()
            ->setMethods(['select', 'where', 'setParameter', 'getQuery', 'getArrayResult'])
            ->getMockForAbstractClass();
    }
}
