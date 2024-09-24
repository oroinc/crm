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
use Oro\Bundle\EntityExtendBundle\Entity\EnumOption;
use Oro\Bundle\SalesBundle\Dashboard\Provider\OpportunityByStatusProvider;
use Oro\Bundle\SalesBundle\Entity\Opportunity;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

class OpportunityByStatusProviderTest extends TestCase
{
    private array $opportunityStatuses = [
        ['id' => 'opportunity_status.won'],
        ['id' => 'opportunity_status.identification_alignment'],
        ['id' => 'opportunity_status.in_progress'],
        ['id' => 'opportunity_status.needs_analysis'],
        ['id' => 'opportunity_status.negotiation'],
        ['id' => 'opportunity_status.solution_development'],
        ['id' => 'opportunity_status.lost']
    ];

    private array $translations = [
        'oro.entity_extend.enum_option.opportunity_status.won' => 'Closed Won',
        'oro.entity_extend.enum_option.opportunity_status.identification_alignment' => 'Identification & Alignment',
        'oro.entity_extend.enum_option.opportunity_status.in_progress' => 'Open',
        'oro.entity_extend.enum_option.opportunity_status.needs_analysis' => 'Needs Analysis',
        'oro.entity_extend.enum_option.opportunity_status.negotiation' => 'Negotiation',
        'oro.entity_extend.enum_option.opportunity_status.solution_development' => 'Solution Development',
        'oro.entity_extend.enum_option.opportunity_status.lost' => 'Closed Lost'
    ];

    /** @var ManagerRegistry|MockObject */
    private $doctrine;

    /** @var AclHelper|MockObject */
    private $aclHelper;

    /** @var OpportunityByStatusProvider */
    private $provider;

    #[\Override]
    protected function setUp(): void
    {
        $this->doctrine = $this->createMock(ManagerRegistry::class);
        $this->aclHelper = $this->createMock(AclHelper::class);

        $translator = $this->createMock(TranslatorInterface::class);
        $translator->expects($this->any())
            ->method('trans')
            ->willReturnCallback(function ($key) {
                return $this->translations[$key];
            });

        $this->provider = new OpportunityByStatusProvider(
            $this->doctrine,
            $this->aclHelper,
            $this->createMock(WidgetProviderFilterManager::class),
            $this->createMock(DateFilterProcessor::class),
            $this->createMock(CurrencyQueryBuilderTransformerInterface::class),
            $translator,
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
            ->with('s.id')
            ->willReturnSelf();
        $statusesQB->expects($this->once())
            ->method('andWhere')
            ->with('s.enumCode = :enumCode')
            ->willReturnSelf();
        $statusesQB->expects($this->once())
            ->method('setParameter')
            ->with('enumCode', Opportunity::INTERNAL_STATUS_CODE)
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
                [Opportunity::class],
                [EnumOption::class]
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
            'request quantities' => [
                'widgetOptions' => new WidgetOptionBag([
                    'excluded_statuses' => [],
                    'useQuantityAsData' => true
                ]),
                'expected DQL' =>
                    'SELECT JSON_EXTRACT(o.serialized_data, \'status\') as status, COUNT(o.id) as quantity '
                    . 'FROM Oro\Bundle\SalesBundle\Entity\Opportunity o '
                    . 'GROUP BY status '
                    . 'ORDER BY quantity DESC'
            ],
            'request quantities with excluded statuses - should not affect DQL' => [
                'widgetOptions' => new WidgetOptionBag([
                    'excluded_statuses' => ['in_progress', 'won'],
                    'useQuantityAsData' => true
                ]),
                'expected DQL' =>
                    'SELECT JSON_EXTRACT(o.serialized_data, \'status\') as status, COUNT(o.id) as quantity '
                    . 'FROM Oro\Bundle\SalesBundle\Entity\Opportunity o '
                    . 'GROUP BY status '
                    . 'ORDER BY quantity DESC'
            ],
            'request budget amounts' => [
                'widgetOptions' => new WidgetOptionBag([
                    'excluded_statuses' => [],
                    'useQuantityAsData' => false
                ]),
                'expected DQL' => <<<DQL
SELECT JSON_EXTRACT(o.serialized_data, 'status') as status, SUM(
                        CASE WHEN JSON_EXTRACT(o.serialized_data, 'status') = 'opportunity_status.won'
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
                'expected DQL' => <<<DQL
SELECT JSON_EXTRACT(o.serialized_data, 'status') as status, SUM(
                        CASE WHEN JSON_EXTRACT(o.serialized_data, 'status') = 'opportunity_status.won'
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
        array           $result,
        array           $expected
    ) {
        $opportunityQB = new QueryBuilder($this->createMock(EntityManagerInterface::class));
        $opportunityQB->from(Opportunity::class, 'o');

        $statusesQuery = $this->createMock(AbstractQuery::class);
        $statusesQB = $this->createMock(QueryBuilder::class);
        $translatorMock = $this->createMock(TranslatorInterface::class);
        $statusesQB->expects($this->once())
            ->method('select')
            ->with('s.id')
            ->willReturnSelf();
        $statusesQB->expects($this->once())
            ->method('andWhere')
            ->with('s.enumCode = :enumCode')
            ->willReturnSelf();
        $statusesQB->expects($this->once())
            ->method('setParameter')
            ->with('enumCode', Opportunity::INTERNAL_STATUS_CODE)
            ->willReturnSelf();
        $statusesQB->expects($this->once())
            ->method('getQuery')
            ->willReturn($statusesQuery);
        $statusesQuery->expects($this->once())
            ->method('getArrayResult')
            ->willReturn($this->opportunityStatuses);
        $translatorMock->expects($this->any())
            ->method('trans')
            ->willReturnCallback(function ($key) {
                foreach ($this->translations as $translation) {
                    if (array_key_exists($key, $translation)) {
                        return $translation[$key];
                    }
                }
                // Если перевод не найден, просто возвращаем ключ
                return $key;
            });


        $repository = $this->createMock(EntityRepository::class);
        $repository->expects($this->exactly(2))
            ->method('createQueryBuilder')
            ->withConsecutive(['o'], ['s'])
            ->willReturnOnConsecutiveCalls($opportunityQB, $statusesQB);

        $this->doctrine->expects($this->exactly(2))
            ->method('getRepository')
            ->withConsecutive(
                [Opportunity::class],
                [EnumOption::class]
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

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function getOpportunitiesGroupedByStatusResultDataProvider(): array
    {
        return [
            'result with all statuses, no exclusions - only labels should be added' => [
                'widgetOptions' => new WidgetOptionBag([
                    'excluded_statuses' => [],
                    'useQuantityAsData' => true
                ]),
                'result data' => [
                    0 => ['quantity' => 700, 'status' => 'opportunity_status.won'],
                    1 => ['quantity' => 600, 'status' => 'opportunity_status.identification_alignment'],
                    2 => ['quantity' => 500, 'status' => 'opportunity_status.in_progress'],
                    3 => ['quantity' => 400, 'status' => 'opportunity_status.needs_analysis'],
                    4 => ['quantity' => 300, 'status' => 'opportunity_status.negotiation'],
                    5 => ['quantity' => 200, 'status' => 'opportunity_status.solution_development'],
                    6 => ['quantity' => 100, 'status' => 'opportunity_status.lost'],
                ],
                'expected formatted result' => [
                    0 => ['quantity' => 700, 'status' => 'opportunity_status.won', 'label' => 'Closed Won'],
                    1 => [
                        'quantity' => 600,
                        'status' => 'opportunity_status.identification_alignment',
                        'label' => 'Identification & Alignment'
                    ],
                    2 => ['quantity' => 500, 'status' => 'opportunity_status.in_progress', 'label' => 'Open'],
                    3 => [
                        'quantity' => 400,
                        'status' => 'opportunity_status.needs_analysis',
                        'label' => 'Needs Analysis'
                    ],
                    4 => ['quantity' => 300, 'status' => 'opportunity_status.negotiation', 'label' => 'Negotiation'],
                    5 => [
                        'quantity' => 200,
                        'status' => 'opportunity_status.solution_development',
                        'label' => 'Solution Development'
                    ],
                    6 => ['quantity' => 100, 'status' => 'opportunity_status.lost', 'label' => 'Closed Lost'],
                ]
            ],
            'result with all statuses, with exclusions - excluded should be removed, labels' => [
                'widgetOptions' => new WidgetOptionBag([
                    'excluded_statuses' => [
                        'opportunity_status.identification_alignment',
                        'opportunity_status.solution_development'
                    ],
                    'useQuantityAsData' => true
                ]),
                'result data' => [
                    0 => ['quantity' => 700, 'status' => 'opportunity_status.won'],
                    1 => ['quantity' => 600, 'status' => 'opportunity_status.identification_alignment'],
                    2 => ['quantity' => 500, 'status' => 'opportunity_status.in_progress'],
                    3 => ['quantity' => 400, 'status' => 'opportunity_status.needs_analysis'],
                    4 => ['quantity' => 300, 'status' => 'opportunity_status.negotiation'],
                    5 => ['quantity' => 200, 'status' => 'opportunity_status.solution_development'],
                    6 => ['quantity' => 100, 'status' => 'opportunity_status.lost'],
                ],
                'expected formatted result' => [
                    0 => ['quantity' => 700, 'status' => 'opportunity_status.won', 'label' => 'Closed Won'],
                    2 => ['quantity' => 500, 'status' => 'opportunity_status.in_progress', 'label' => 'Open'],
                    3 => [
                        'quantity' => 400,
                        'status' => 'opportunity_status.needs_analysis',
                        'label' => 'Needs Analysis'
                    ],
                    4 => ['quantity' => 300, 'status' => 'opportunity_status.negotiation', 'label' => 'Negotiation'],
                    6 => ['quantity' => 100, 'status' => 'opportunity_status.lost', 'label' => 'Closed Lost'],
                ]
            ],
            'result with NOT all statuses, no exclusions - all statuses, labels' => [
                'widgetOptions' => new WidgetOptionBag([
                    'excluded_statuses' => [],
                    'useQuantityAsData' => true
                ]),
                'result data' => [
                    0 => ['quantity' => 700, 'status' => 'opportunity_status.won'],
                    1 => ['quantity' => 300, 'status' => 'opportunity_status.negotiation'],
                ],
                'expected formatted result' => [
                    0 => ['quantity' => 700, 'status' => 'opportunity_status.won', 'label' => 'Closed Won'],
                    1 => ['quantity' => 300, 'status' => 'opportunity_status.negotiation', 'label' => 'Negotiation'],
                    2 => [
                        'quantity' => 0,
                        'status' => 'opportunity_status.identification_alignment',
                        'label' => 'Identification & Alignment'
                    ],
                    3 => ['quantity' => 0, 'status' => 'opportunity_status.in_progress', 'label' => 'Open'],
                    4 => [
                        'quantity' => 0,
                        'status' => 'opportunity_status.needs_analysis',
                        'label' => 'Needs Analysis'
                    ],
                    5 => [
                        'quantity' => 0,
                        'status' => 'opportunity_status.solution_development',
                        'label' => 'Solution Development'
                    ],
                    6 => ['quantity' => 0, 'status' => 'opportunity_status.lost', 'label' => 'Closed Lost'],
                ]
            ],
            'result with NOT all statuses AND exclusions - all statuses(except excluded), labels' => [
                'widgetOptions' => new WidgetOptionBag([
                    'excluded_statuses' => [
                        'opportunity_status.identification_alignment',
                        'opportunity_status.lost',
                        'opportunity_status.in_progress'
                    ],
                    'useQuantityAsData' => true
                ]),
                'result data' => [
                    0 => ['quantity' => 700, 'status' => 'opportunity_status.won'],
                    1 => ['quantity' => 500, 'status' => 'opportunity_status.in_progress'],
                    2 => ['quantity' => 300, 'status' => 'opportunity_status.negotiation'],
                    3 => ['quantity' => 100, 'status' => 'opportunity_status.lost'],
                ],
                'expected formatted result' => [
                    0 => ['quantity' => 700, 'status' => 'opportunity_status.won', 'label' => 'Closed Won'],
                    2 => ['quantity' => 300, 'status' => 'opportunity_status.negotiation', 'label' => 'Negotiation'],
                    4 => [
                        'quantity' => 0,
                        'status' => 'opportunity_status.needs_analysis',
                        'label' => 'Needs Analysis'
                    ],
                    5 => [
                        'quantity' => 0,
                        'status' => 'opportunity_status.solution_development',
                        'label' => 'Solution Development'
                    ],
                ]
            ],
        ];
    }
}
