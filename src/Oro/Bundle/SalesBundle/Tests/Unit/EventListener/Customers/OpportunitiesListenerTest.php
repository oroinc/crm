<?php

namespace Oro\Bundle\SalesBundle\Tests\Unit\EventListener\Customers;

use Symfony\Component\Translation\TranslatorInterface;

use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\EntityBundle\Tests\Unit\ORM\Fixtures\TestEntity;

use Oro\Bundle\EntityConfigBundle\Config\Config;
use Oro\Bundle\EntityConfigBundle\Config\Id\EntityConfigId;
use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;

use Oro\Bundle\UIBundle\Event\BeforeViewRenderEvent;

use Oro\Bundle\SalesBundle\EventListener\Customers\OpportunitiesListener;

class OpportunitiesListenerTest extends \PHPUnit_Framework_TestCase
{
    /** @var OpportunitiesListener */
    protected $listener;

    /** @var ConfigProvider|\PHPUnit_Framework_MockObject_MockObject */
    protected $opportunityProvider;

    /** @var TranslatorInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $translator;

    /** @var DoctrineHelper|\PHPUnit_Framework_MockObject_MockObject */
    protected $doctrineHelper;

    public function setUp()
    {
        $configManager             = $this
            ->getMockBuilder('Oro\Bundle\EntityConfigBundle\Config\ConfigManager')
            ->disableOriginalConstructor()
            ->getMock();
        $this->opportunityProvider = $this
            ->getMockBuilder('Oro\Bundle\EntityConfigBundle\Config\ConfigProvider')
            ->disableOriginalConstructor()
            ->setMethods(['hasConfig', 'getConfig'])
            ->getMock();
        $this->doctrineHelper = $this
            ->getMockBuilder('Oro\Bundle\EntityBundle\ORM\DoctrineHelper')
            ->disableOriginalConstructor()
            ->setMethods(['getSingleEntityIdentifier'])
            ->getMock();
        $configManager
            ->expects($this->any())
            ->method('getProvider')
            ->with('opportunity')
            ->willReturn($this->opportunityProvider);
        $this->translator = $this->getMock(TranslatorInterface::class);
        $this->listener = new OpportunitiesListener($configManager, $this->translator, $this->doctrineHelper);
    }

    public function testAddOpportunities()
    {
        $id = 5;
        $entity = new TestEntity($id);
        $customerClass = TestEntity::class;
        $opportunitiesData = 'Opportunities List';
        $opportunitiesTitle = 'Opportunities Title';

        $env = $this->getMockBuilder('Twig_Environment')
            ->disableOriginalConstructor()
            ->getMock();

        $env->expects($this->once())
            ->method('render')
            ->with(
                'OroSalesBundle:Customer:opportunitiesGrid.html.twig',
                ['gridParams' => ['customer_id' => $id, 'customer_class' => $customerClass]]
            )
            ->willReturn($opportunitiesData);

        $this->doctrineHelper
            ->expects($this->once())
            ->method('getSingleEntityIdentifier')
            ->with($entity)
            ->willReturn($id);

        $this->translator->expects($this->once())
            ->method('trans')
            ->with('oro.sales.customers.opportunities.grid.label')
            ->willReturn($opportunitiesTitle);

        $data = ['dataBlocks' => ['subblocks' => ['title' => 'some title', 'data' => 'some data']]];
        $event = new BeforeViewRenderEvent($env, $data, $entity);
        $this->prepareEntityConfigs($entity, true, true);
        $this->listener->addOpportunities($event);
        $data['dataBlocks'][] = [
            'title' => $opportunitiesTitle,
            'subblocks' => [['data' => [$opportunitiesData]]],
            'priority' => 100
        ];
        $this->assertEquals(
            $data,
            $event->getData()
        );
    }

    /**
     * @dataProvider testAddOpportunitiesNotCustomerDataProvider
     *
     * @param object|null $entity
     * @param bool|null $hasConfig
     * @param bool|null $enabled
     *
     */
    public function testAddOpportunitiesNotCustomer($entity = null, $hasConfig = null, $enabled = null)
    {
        $env = $this->getMockBuilder('Twig_Environment')
            ->disableOriginalConstructor()
            ->getMock();
        $data = ['dataBlocks' => ['subblocks' => ['title' => 'some title', 'data' => 'some data']]];
        $event = new BeforeViewRenderEvent($env, $data, $entity);

        $this->prepareEntityConfigs($entity, $hasConfig, $enabled);
        $this->listener->addOpportunities($event);
        $this->assertEquals(
            $data,
            $event->getData()
        );
    }

    /**
     * @param object    $entity
     * @param bool|null $hasConfig
     * @param bool|null $enabled
     */
    protected function prepareEntityConfigs($entity, $hasConfig = null, $enabled = null)
    {
        if (null !== $hasConfig) {
            $this->opportunityProvider
                ->expects($this->once())
                ->method('hasConfig')
                ->with($entity)
                ->willReturn($hasConfig);
            if (null !== $enabled) {
                $customerClass = get_class($entity);
                $configId  = new EntityConfigId('opportunity', $customerClass);
                $config    = new Config($configId, ['enabled' => $enabled]);
                $this->opportunityProvider
                    ->expects($this->once())
                    ->method('getConfig')
                    ->with($entity)
                    ->willReturn($config);
            }
        }
    }

    public function testAddOpportunitiesNotCustomerDataProvider()
    {
        return [
            'no entity' => [],
            'no opportunity configs' => [new TestEntity(), false],
            'not enabled opportunity' => [new TestEntity(), true, false]
        ];
    }
}
