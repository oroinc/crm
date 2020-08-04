<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\ImportExport\Strategy;

use Doctrine\ORM\EntityManager;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\MagentoBundle\Entity\Customer;
use Oro\Bundle\MagentoBundle\Entity\CustomerGroup;
use Oro\Bundle\MagentoBundle\Entity\Store;
use Oro\Bundle\MagentoBundle\Entity\Website;
use Oro\Bundle\MagentoBundle\ImportExport\Strategy\GuestCustomerStrategy;
use Oro\Bundle\MagentoBundle\ImportExport\Strategy\StrategyHelper\GuestCustomerStrategyHelper;
use Oro\Bundle\OrganizationBundle\Ownership\EntityOwnershipAssociationsSetter;

class GuestCustomerStrategyTest extends AbstractStrategyTest
{
    const DEFAULT_CHANNEL_ID = 1;

    /** @var  GuestCustomerStrategyHelper */
    private $guestCustomerStrategyHelper;

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
            $this->newEntitiesHelper,
            $this->doctrineHelper,
            $this->relatedEntityStateHelper
        );

        $strategy->setOwnerHelper($this->defaultOwnerHelper);
        $strategy->setChannelHelper($this->channelHelper);

        $this->guestCustomerStrategyHelper = $this->createMock(GuestCustomerStrategyHelper::class);
        $this->guestCustomerStrategyHelper->expects($this->any())
            ->method('getUpdatedSearchContextForGuestCustomers')
            ->willReturn([]);
        $strategy->setGuestCustomerStrategyHelper($this->guestCustomerStrategyHelper);

        $this->databaseHelper->expects($this->any())->method('getEntityReference')
            ->will($this->returnArgument(0));

        $strategy->setImportExportContext(
            $this->getMockBuilder('Oro\Bundle\ImportExportBundle\Context\ContextInterface')
            ->disableOriginalConstructor()
            ->getMock()
        );
        $strategy->setEntityName('Oro\Bundle\MagentoBundle\Entity\Customer');
        $strategy->setOwnershipSetter($this->createMock(EntityOwnershipAssociationsSetter::class));

        return $strategy;
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();
        unset($this->guestCustomerStrategyHelper);
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

    public function testProcessNewGuestCustomerWithStore()
    {
        $store = new Store();
        $store->setWebsite(new Website());

        $customer = $this->getCustomer();
        $customer->setGuest(true);
        $customer->setId(1);
        $customer->setEmail('test@example.com');
        $customer->setStore($store);

        $group = new CustomerGroup();
        $group->setId(0);
        $customer->setGroup($group);

        /** @var Customer $result */
        $result = $this->getStrategy()->process($customer);

        $this->assertNotEmpty($result);
        $this->assertEquals($result->getGroup(), $group);
        $this->assertTrue($result->isGuest());
    }

    public function testProcessExistingGuestCustomer()
    {
        $website = new Website();
        $website->setId(1);
        $website->setOriginId(1);

        $store = new Store();
        $store->setWebsite($website);

        $customer = $this->getCustomer();
        $customer->setGuest(true);
        $customer->setId(1);
        $customer->setEmail('test@example.com');
        $customer->setStore($store);

        $group = new CustomerGroup();
        $group->setId(0);
        $customer->setGroup($group);

        /** @var Customer $result */
        $result = $this->getStrategy()->process($customer);

        $this->assertNotEmpty($result);
        $this->assertEquals($result->getGroup(), $group);
        $this->assertTrue($result->isGuest());
    }

    /**
     * @return Customer
     */
    private function getCustomer()
    {
        $customer = new Customer();
        $channel = $this->createMock(Channel::class);

        $customer->setChannel($channel);

        return $customer;
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|EntityManager
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
