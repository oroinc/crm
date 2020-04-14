<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\EventListener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Oro\Bundle\LocaleBundle\Model\LocaleSettings;
use Oro\Bundle\MagentoBundle\Entity\Customer;
use Oro\Bundle\MagentoBundle\EventListener\CustomerCurrencyListener;

class CustomerCurrencyListenerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var LocaleSettings|\PHPUnit\Framework\MockObject\MockObject
     */
    private $localeSettings;

    /**
     * @var CustomerCurrencyListener
     */
    private $listener;

    protected function setUp(): void
    {
        $this->localeSettings = $this->createMock(LocaleSettings::class);
        $this->listener = new CustomerCurrencyListener($this->localeSettings);
    }

    public function testPrePersistEntityWithCurrency()
    {
        /** @var Customer|\PHPUnit\Framework\MockObject\MockObject $entity */
        $entity = $this->createMock(Customer::class);
        $entity->expects($this->once())
            ->method('getCurrency')
            ->will($this->returnValue('USD'));
        $entity->expects($this->never())
            ->method('setCurrency');
        $this->localeSettings->expects($this->never())
            ->method($this->anything());

        /** @var LifecycleEventArgs|\PHPUnit\Framework\MockObject\MockObject $event */
        $event = $this->createMock(LifecycleEventArgs::class);

        $this->listener->prePersist($entity, $event);
    }

    public function testPrePersistEntitySetCurrency()
    {
        $currency = 'EUR';

        $this->localeSettings->expects($this->once())
            ->method('getCurrency')
            ->will($this->returnValue($currency));

        /** @var Customer|\PHPUnit\Framework\MockObject\MockObject $entity */
        $entity = $this->createMock(Customer::class);
        $entity->expects($this->once())
            ->method('getCurrency');
        $entity->expects($this->once())
            ->method('setCurrency')
            ->with($currency);

        /** @var LifecycleEventArgs|\PHPUnit\Framework\MockObject\MockObject $event */
        $event = $this->createMock(LifecycleEventArgs::class);

        $this->listener->prePersist($entity, $event);
    }
}
