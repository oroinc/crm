<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Unit\EventListener;

use OroCRM\Bundle\MagentoBundle\EventListener\CustomerCurrencyListener;

class CustomerCurrencyListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $localeSettings;

    /**
     * @var CustomerCurrencyListener
     */
    protected $listener;

    protected function setUp()
    {
        $this->localeSettings = $this->getMockBuilder('Oro\Bundle\LocaleBundle\Model\LocaleSettings')
            ->disableOriginalConstructor()
            ->getMock();
        $container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');

        $container->expects($this->any())
            ->method('get')
            ->with('oro_locale.settings')
            ->will($this->returnValue($this->localeSettings));

        $this->listener = new CustomerCurrencyListener();
        $this->listener->setContainer($container);
    }

    public function testPrePersistIncorrectEntity()
    {
        $entity = $this
            ->getMockBuilder('\stdClass')
            ->setMethods(['setCurrency'])
            ->getMock()
        ;
        $entity->expects($this->never())
            ->method('setCurrency');

        $event = $this->getMockBuilder('Doctrine\ORM\Event\LifecycleEventArgs')
            ->disableOriginalConstructor()
            ->getMock();
        $event->expects($this->once())
            ->method('getEntity')
            ->will($this->returnValue($entity));

        $this->listener->prePersist($event);
    }

    public function testPrePersistEntityWithCurrency()
    {
        $entity = $this->getMockBuilder('OroCRM\Bundle\MagentoBundle\Entity\Customer')
            ->disableOriginalConstructor()
            ->getMock();
        $entity->expects($this->once())
            ->method('getCurrency')
            ->will($this->returnValue('USD'));
        $entity->expects($this->never())
            ->method('setCurrency');
        $this->localeSettings->expects($this->never())
            ->method($this->anything());

        $event = $this->getMockBuilder('Doctrine\ORM\Event\LifecycleEventArgs')
            ->disableOriginalConstructor()
            ->getMock();
        $event->expects($this->once())
            ->method('getEntity')
            ->will($this->returnValue($entity));

        $this->listener->prePersist($event);
    }

    public function testPrePersistEntitySetCurrency()
    {
        $currency = 'EUR';

        $this->localeSettings->expects($this->once())
            ->method('getCurrency')
            ->will($this->returnValue($currency));

        $entity = $this->getMockBuilder('OroCRM\Bundle\MagentoBundle\Entity\Customer')
            ->disableOriginalConstructor()
            ->getMock();
        $entity->expects($this->once())
            ->method('getCurrency');
        $entity->expects($this->once())
            ->method('setCurrency')
            ->with($currency);

        $event = $this->getMockBuilder('Doctrine\ORM\Event\LifecycleEventArgs')
            ->disableOriginalConstructor()
            ->getMock();
        $event->expects($this->once())
            ->method('getEntity')
            ->will($this->returnValue($entity));

        $this->listener->prePersist($event);
    }
}
