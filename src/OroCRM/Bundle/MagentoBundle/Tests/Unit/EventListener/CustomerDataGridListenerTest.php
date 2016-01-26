<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Unit\EventListener;

use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datagrid\ParameterBag;
use Oro\Bundle\DataGridBundle\Event\PreBuild;
use OroCRM\Bundle\MagentoBundle\EventListener\CustomerDataGridListener;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class CustomerDataGridListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|Request
     */
    protected $request;

    /**
     * @var CustomerDataGridListener
     */
    protected $listener;

    protected function setUp()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|RequestStack $requestStack */
        $requestStack = $this->getMockBuilder('Symfony\Component\HttpFoundation\RequestStack')
            ->disableOriginalConstructor()
            ->getMock();
        $this->request = new Request();
        $requestStack->expects($this->once())
            ->method('getCurrentRequest')
            ->will($this->returnValue($this->request));

        $this->listener = new CustomerDataGridListener($requestStack);
    }

    public function testWithoutFilter()
    {
        $config = DatagridConfiguration::create([]);
        $parameters = new ParameterBag();
        $event = new PreBuild($config, $parameters);

        $this->listener->onPreBuild($event);
        $this->assertArrayHasKey('isSubscriber', $config->offsetGetByPath('[filters][columns]'));
    }

    public function testWithFilter()
    {
        $this->request->query->set('magento-customers-grid', ['_filter' => ['isSubscriber' => ['value' => 'yes']]]);

        $config = DatagridConfiguration::create([]);
        $config->offsetSetByPath('[source][query][select]', ['c.id', 'c.firstName']);
        $parameters = new ParameterBag();
        $event = new PreBuild($config, $parameters);

        $this->assertEmpty($config->offsetGetByPath('[source][query][join][left]'));
        $this->listener->onPreBuild($event);
        $this->assertArrayHasKey('isSubscriber', $config->offsetGetByPath('[filters][columns]'));
        $this->assertEquals('DISTINCT c.id', $config->offsetGetByPath('[source][query][select][0]'));
        $this->assertContains('isSubscriber', $config->offsetGetByPath('[source][query][select][2]'));
        $this->assertCount(3, $config->offsetGetByPath('[source][query][join][left]'));
    }
}
