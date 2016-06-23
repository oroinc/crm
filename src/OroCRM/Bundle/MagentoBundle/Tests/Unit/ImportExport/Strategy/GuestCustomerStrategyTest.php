<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Unit\ImportExport\Strategy;

use Oro\Bundle\IntegrationBundle\Entity\Channel;
use OroCRM\Bundle\MagentoBundle\Entity\Customer;
use OroCRM\Bundle\MagentoBundle\Entity\Store;
use OroCRM\Bundle\MagentoBundle\Entity\Website;
use OroCRM\Bundle\MagentoBundle\ImportExport\Strategy\GuestCustomerStrategy;

class GuestCustomerStrategyTest extends AbstractStrategyTest
{
    /**
     * @return GuestCustomerStrategy
     */
    protected function getStrategy()
    {
        $strategy = new GuestCustomerStrategy(
            $this->eventDispatcher,
            $this->strategyHelper,
            $this->fieldHelper,
            $this->databaseHelper,
            $this->chainEntityClassNameProvider,
            $this->translator,
            $this->newEntitiesHelper
        );

        $strategy->setOwnerHelper($this->defaultOwnerHelper);
        $strategy->setChannelHelper($this->channelHelper);

        $this->databaseHelper->expects($this->any())->method('getEntityReference')
            ->will($this->returnArgument(0));

        $strategy->setImportExportContext(
            $this->getMockBuilder('Oro\Bundle\ImportExportBundle\Context\ContextInterface')
            ->disableOriginalConstructor()
            ->getMock()
        );
        $strategy->setEntityName('OroCRM\Bundle\MagentoBundle\Entity\Customer');

        return $strategy;
    }

    public function testProcessEmptyEntity()
    {
        $customer = $this->getCustomer();

        $this->assertNotEmpty($this->getStrategy()->process($customer));
    }

    public function testProcessEntityWithStore()
    {
        $store = new Store();
        $store->setWebsite(new Website());

        $customer = $this->getCustomer();
        $customer->setStore($store);

        $this->assertNotEmpty($this->getStrategy()->process($customer));
    }

    /**
     * @return Customer
     */
    private function getCustomer()
    {
        $customer = new Customer();
        $customer->setChannel(new Channel());

        return $customer;
    }
}
