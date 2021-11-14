<?php

namespace Oro\Bundle\SalesBundle\Tests\Unit\EventListener\Customers;

use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\EntityBundle\Tests\Unit\ORM\Fixtures\TestEntity;
use Oro\Bundle\EntityConfigBundle\Config\ConfigInterface;
use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;
use Oro\Bundle\FeatureToggleBundle\Checker\FeatureChecker;
use Oro\Bundle\SalesBundle\Entity\Opportunity;
use Oro\Bundle\SalesBundle\EventListener\Customers\OpportunitiesListener;
use Oro\Bundle\SalesBundle\Provider\Customer\ConfigProvider as CustomerConfigProvider;
use Oro\Bundle\UIBundle\Event\BeforeViewRenderEvent;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

class OpportunitiesListenerTest extends \PHPUnit\Framework\TestCase
{
    /** @var CustomerConfigProvider|\PHPUnit\Framework\MockObject\MockObject */
    private $provider;

    /** @var TranslatorInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $translator;

    /** @var DoctrineHelper|\PHPUnit\Framework\MockObject\MockObject */
    private $doctrineHelper;

    /** @var ConfigProvider|\PHPUnit\Framework\MockObject\MockObject */
    private $configProvider;

    /** @var OpportunitiesListener */
    private $listener;

    protected function setUp(): void
    {
        $this->provider = $this->createMock(CustomerConfigProvider::class);
        $this->doctrineHelper = $this->createMock(DoctrineHelper::class);
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->configProvider = $this->createMock(ConfigProvider::class);

        $featureChecker = $this->createMock(FeatureChecker::class);
        $featureChecker->expects($this->any())
            ->method('isFeatureEnabled')
            ->with('sales_opportunity')
            ->willReturn(true);

        $this->listener = new OpportunitiesListener(
            $this->provider,
            $this->translator,
            $this->doctrineHelper,
            $this->configProvider,
            $featureChecker
        );
    }

    public function testAddOpportunities()
    {
        $id = 5;
        $entity = new TestEntity($id);
        $customerClass = TestEntity::class;
        $opportunitiesData = 'Opportunities List';
        $opportunitiesTitle = 'Opportunities Title';

        $env = $this->createMock(Environment::class);

        $env->expects($this->once())
            ->method('render')
            ->with(
                '@OroSales/Customer/opportunitiesGrid.html.twig',
                [
                    'gridParams' => [
                        'customer_id' => $id,
                        'customer_class' => $customerClass,
                        'related_entity_class' => Opportunity::class
                    ]
                ]
            )
            ->willReturn($opportunitiesData);

        $this->doctrineHelper->expects($this->once())
            ->method('getSingleEntityIdentifier')
            ->with($entity)
            ->willReturn($id);

        $this->translator->expects($this->once())
            ->method('trans')
            ->with('oro.sales.customers.opportunities.grid.label')
            ->willReturn($opportunitiesTitle);

        $config = $this->createMock(ConfigInterface::class);

        $this->configProvider->expects($this->once())
            ->method('getConfig')
            ->with($customerClass)
            ->willReturn($config);

        $data = [
            'dataBlocks' => [
                'subblocks' => ['title' => 'some title', 'data' => 'some data']
            ]
        ];
        $event = new BeforeViewRenderEvent($env, $data, $entity);
        $this->prepareConfigProvider($entity, true);
        $this->listener->addOpportunities($event);
        $data['dataBlocks'][] =
            [
                'title'     => $opportunitiesTitle,
                'subblocks' => [['data' => [$opportunitiesData]]],
                'priority' => 1010
            ];
        $this->assertEquals(
            $data,
            $event->getData()
        );
    }

    /**
     * @dataProvider testAddOpportunitiesNotCustomerDataProvider
     */
    public function testAddOpportunitiesNotCustomer(object $entity = null, bool $isCustomerClass = null)
    {
        $env = $this->createMock(Environment::class);
        $data = ['dataBlocks' => ['subblocks' => ['title' => 'some title', 'data' => 'some data']]];
        $event = new BeforeViewRenderEvent($env, $data, $entity);

        $this->prepareConfigProvider($entity, $isCustomerClass);
        $this->listener->addOpportunities($event);
        $this->assertEquals(
            $data,
            $event->getData()
        );
    }

    private function prepareConfigProvider(?object $entity, bool $isCustomerClass = null): void
    {
        if (null !== $isCustomerClass) {
            $this->provider->expects($this->once())
                ->method('isCustomerClass')
                ->with($entity)
                ->willReturn($isCustomerClass);
        }
    }

    public function testAddOpportunitiesNotCustomerDataProvider(): array
    {
        return [
            'no entity'          => [],
            'not customer class' => [new TestEntity(), false],
        ];
    }
}
