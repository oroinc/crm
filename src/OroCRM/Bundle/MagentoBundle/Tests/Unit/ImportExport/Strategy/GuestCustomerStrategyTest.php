<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Unit\ImportExport\Strategy;

use Doctrine\ORM\EntityManager;

use Oro\Bundle\IntegrationBundle\Entity\Channel;
use OroCRM\Bundle\MagentoBundle\Entity\Customer;
use OroCRM\Bundle\MagentoBundle\Entity\CustomerGroup;
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

        $entityManager = $this->getMockEntityManager();
        $this->strategyHelper->expects($this->once())->method('getEntityManager')
            ->willReturn($entityManager);

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

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|EntityManager
     */
    private function getMockEntityManager()
    {
        $entityManager = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()->getMock();

        $repository     = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()->getMock();
        $repository->expects(self::once())->method('findOneBy')->willReturn(new CustomerGroup());

        $entityManager->expects(self::once())->method('getRepository')->willReturn($repository);

        return $entityManager;
    }
}
