<?php

namespace Oro\Bundle\SalesBundle\Tests\Unit\Dashboard\Provider;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\CurrencyBundle\Query\CurrencyQueryBuilderTransformerInterface;
use Oro\Bundle\DashboardBundle\Filter\DateFilterProcessor;
use Oro\Bundle\DashboardBundle\Filter\WidgetProviderFilterManager;
use Oro\Bundle\DashboardBundle\Model\WidgetOptionBag;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\SalesBundle\Dashboard\Provider\OpportunityByStatusProvider;
use Oro\Bundle\SalesBundle\Entity\Opportunity;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;

class OpportunityByStatusProviderTest extends \PHPUnit\Framework\TestCase
{
    private array $opportunityStatuses = [
        ['id' => 'won', 'name' => 'Won'],
        ['id' => 'identification_alignment', 'name' => 'Identification'],
        ['id' => 'in_progress', 'name' => 'Open'],
        ['id' => 'needs_analysis', 'name' => 'Analysis'],
        ['id' => 'negotiation', 'name' => 'Negotiation'],
        ['id' => 'solution_development', 'name' => 'Development'],
        ['id' => 'lost', 'name' => 'Lost']
    ];

    /** @var ManagerRegistry|\PHPUnit\Framework\MockObject\MockObject */
    private $doctrine;

    /** @var AclHelper|\PHPUnit\Framework\MockObject\MockObject */
    private $aclHelper;

    /** @var OpportunityByStatusProvider */
    private $provider;

    protected function setUp(): void
    {
        $this->doctrine = $this->createMock(ManagerRegistry::class);
        $this->aclHelper = $this->createMock(AclHelper::class);

        $this->provider = new OpportunityByStatusProvider(
            $this->doctrine,
            $this->aclHelper,
            $this->createMock(WidgetProviderFilterManager::class),
            $this->createMock(DateFilterProcessor::class),
            $this->createMock(CurrencyQueryBuilderTransformerInterface::class)
        );
    }

    /**
     * @dataProvider getOpportunitiesGroupedByStatusDQLDataProvider
     */
    public function testGetOpportunitiesGroupedByStatusDQL(WidgetOptionBag $widgetOptions, string $expectation)
    {
        $opportunityQB = new QueryBuilder($this->createMock(EntityManagerInterface::class));
        $opportunityQB->from(Opportunity::class, 'o');

        $statusesQuery = $this->createMock(AbstractQuery::class);
        $statusesQB = $this->createMock(QueryBuilder::class);
        $statusesQB->expects($this->once())
            ->method('select')
            ->with('s.id, s.name')
            ->willReturnSelf();
        $statusesQB->expects($this->once())
            ->method('getQuery')
            ->willReturn($statusesQuery);
        $statusesQuery->expects($this->once())
            ->method('getArrayResult')
            ->willReturn($this->opportunityStatuses);

        $repository = $this->createMock(EntityRepository::class);
        $repository->expects($this->exactly(2))
            ->method('createQueryBuilder')
            ->withConsecutive(['o'], ['s'])
            ->willReturnOnConsecutiveCalls($opportunityQB, $statusesQB);

        $this->doctrine->expects($this->exactly(2))
            ->method('getRepository')
            ->withConsecutive(
                ['OroSalesBundle:Opportunity'],
                [ExtendHelper::buildEnumValueClassName('opportunity_status')]
            )
            ->willReturn($repository);

        $query = $this->createMock(AbstractQuery::class);
        $query->expects($this->once())
            ->method('getArrayResult')
            ->willReturn([]);

        $self = $this;
        $this->aclHelper->expects($this->once())
            ->method('apply')
            ->with(
                $this->callback(function (QueryBuilder $query) use ($self, $expectation) {
                    $self->assertEquals($expectation, $query->getDQL());

                    return true;
                })
            )
            ->willReturn($query);

        $this->provider->getOpportunitiesGroupedByStatus($widgetOptions);
    }

    public function getOpportunitiesGroupedByStatusDQLDataProvider(): array
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
     * @dataProvider getOpportunitiesGroupedByStatusResultDataProvider
     */
    public function testGetOpportunitiesGroupedByStatusResultFormatter(
        WidgetOptionBag $widgetOptions,
        array $result,
        array $expected
    ) {
        $opportunityQB = new QueryBuilder($this->createMock(EntityManagerInterface::class));
        $opportunityQB->from(Opportunity::class, 'o');

        $statusesQuery = $this->createMock(AbstractQuery::class);
        $statusesQB = $this->createMock(QueryBuilder::class);
        $statusesQB->expects($this->once())
            ->method('select')
            ->with('s.id, s.name')
            ->willReturnSelf();
        $statusesQB->expects($this->once())
            ->method('getQuery')
            ->willReturn($statusesQuery);
        $statusesQuery->expects($this->once())
            ->method('getArrayResult')
            ->willReturn($this->opportunityStatuses);

        $repository = $this->createMock(EntityRepository::class);
        $repository->expects($this->exactly(2))
            ->method('createQueryBuilder')
            ->withConsecutive(['o'], ['s'])
            ->willReturnOnConsecutiveCalls($opportunityQB, $statusesQB);

        $this->doctrine->expects($this->exactly(2))
            ->method('getRepository')
            ->withConsecutive(
                ['OroSalesBundle:Opportunity'],
                [ExtendHelper::buildEnumValueClassName('opportunity_status')]
            )
            ->willReturn($repository);

        $query = $this->createMock(AbstractQuery::class);
        $query->expects($this->once())
            ->method('getArrayResult')
            ->willReturn($result);

        $this->aclHelper->expects($this->once())
            ->method('apply')
            ->willReturn($query);

        $data = $this->provider->getOpportunitiesGroupedByStatus($widgetOptions);

        $this->assertEquals($expected, $data);
    }

    public function getOpportunitiesGroupedByStatusResultDataProvider(): array
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
}
