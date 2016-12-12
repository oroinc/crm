<?php

namespace Oro\Bundle\SalesBundle\Tests\Unit\EventListener\Customers;

use Symfony\Component\Translation\TranslatorInterface;

use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\EntityBundle\Tests\Unit\ORM\Fixtures\TestEntity;
use Oro\Bundle\EntityConfigBundle\Config\ConfigInterface;
use Oro\Bundle\UIBundle\Event\BeforeViewRenderEvent;

use Oro\Bundle\SalesBundle\EventListener\Customers\OpportunitiesListener;
use Oro\Bundle\SalesBundle\Provider\Customer\AccountConfigProvider;

class OpportunitiesListenerTest extends \PHPUnit_Framework_TestCase
{
    /** @var OpportunitiesListener */
    protected $listener;

    /** @var AccountConfigProvider|\PHPUnit_Framework_MockObject_MockObject */
    protected $provider;

    /** @var TranslatorInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $translator;

    /** @var DoctrineHelper|\PHPUnit_Framework_MockObject_MockObject */
    protected $doctrineHelper;

    /** @var \Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider|\PHPUnit_Framework_MockObject_MockObject */
    protected $configProvider;

    public function setUp()
    {
        $this->provider       = $this
            ->getMockBuilder('Oro\Bundle\SalesBundle\Provider\Customer\ConfigProvider')
            ->disableOriginalConstructor()
            ->setMethods(['isCustomerClass'])
            ->getMock();
        $this->doctrineHelper = $this
            ->getMockBuilder('Oro\Bundle\EntityBundle\ORM\DoctrineHelper')
            ->disableOriginalConstructor()
            ->setMethods(['getSingleEntityIdentifier'])
            ->getMock();

        $this->translator     = $this->getMock(TranslatorInterface::class);

        $this->configProvider = $this->getMockBuilder('Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider')
            ->disableOriginalConstructor()
            ->getMock();

        $this->listener = new OpportunitiesListener(
            $this->provider,
            $this->translator,
            $this->doctrineHelper,
            $this->configProvider
        );
    }

    public function testAddOpportunities()
    {
        $id                 = 5;
        $entity             = new TestEntity($id);
        $customerClass      = TestEntity::class;
        $opportunitiesData  = 'Opportunities List';
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

        $config = $this->getMock(ConfigInterface::class);

        $this->configProvider->expects($this->once())
            ->method('getConfig')
            ->with($customerClass)
            ->willReturn($config);

        $data  = [
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
     *
     * @param object|null $entity
     * @param string|null $isCustomerClass
     */
    public function testAddOpportunitiesNotCustomer($entity = null, $isCustomerClass = null)
    {
        $env   = $this->getMockBuilder('Twig_Environment')
            ->disableOriginalConstructor()
            ->getMock();
        $data  = ['dataBlocks' => ['subblocks' => ['title' => 'some title', 'data' => 'some data']]];
        $event = new BeforeViewRenderEvent($env, $data, $entity);

        $this->prepareConfigProvider($entity, $isCustomerClass);
        $this->listener->addOpportunities($event);
        $this->assertEquals(
            $data,
            $event->getData()
        );
    }

    /**
     * @param object $entity
     * @param null   $isCustomerClass
     */
    protected function prepareConfigProvider($entity, $isCustomerClass = null)
    {
        if (null !== $isCustomerClass) {
            $this->provider
                ->expects($this->once())
                ->method('isCustomerClass')
                ->with($entity)
                ->willReturn($isCustomerClass);
        }
    }

    public function testAddOpportunitiesNotCustomerDataProvider()
    {
        return [
            'no entity'          => [],
            'not customer class' => [new TestEntity(), false],
        ];
    }
}
