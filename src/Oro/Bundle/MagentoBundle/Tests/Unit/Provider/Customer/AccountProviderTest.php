<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\Provider\Customer;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\ImportExportBundle\Strategy\Import\NewEntitiesHelper;
use Oro\Bundle\MagentoBundle\Entity\Customer;
use Oro\Bundle\MagentoBundle\Provider\Customer\AccountProvider;
use Oro\Bundle\MagentoBundle\Service\AutomaticDiscovery;
use Symfony\Component\DependencyInjection\ContainerInterface;

class AccountProviderTest extends \PHPUnit_Framework_TestCase
{
    /** @var AutomaticDiscovery|\PHPUnit_Framework_MockObject_MockObject */
    private $automaticDiscovery;

    /** @var NewEntitiesHelper|\PHPUnit_Framework_MockObject_MockObject */
    private $newEntitiesHelper;

    /** @var AccountProvider */
    private $provider;

    protected function setUp()
    {
        $this->automaticDiscovery = $this->createMock(AutomaticDiscovery::class);
        $this->newEntitiesHelper = $this->createMock(NewEntitiesHelper::class);

        /** @var ContainerInterface|\PHPUnit_Framework_MockObject_MockObject $container */
        $container = $this->createMock(ContainerInterface::class);
        $container->expects($this->any())
            ->method('get')
            ->willReturnMap(
                [
                    [
                        'oro_magento.service.automatic_discovery',
                        ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
                        $this->automaticDiscovery
                    ],
                    [
                        'doctrine',
                        ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE,
                        $this->getDoctrineMock()
                    ]
                ]
            );

        $this->provider = new AccountProvider($this->newEntitiesHelper);
        $this->provider->setContainer($container);
    }

    public function testGetAccountForUnsupportedCustomer()
    {
        $targetCustomer = new \stdClass();

        $this->assertNull($this->provider->getAccount($targetCustomer));
    }

    public function testGetAccountForCustomerWithAccount()
    {
        $account = new Account();

        $targetCustomer = new Customer();
        $targetCustomer->setAccount($account);

        $this->assertSame($account, $this->provider->getAccount($targetCustomer));
    }

    public function testGetAccount()
    {
        $targetCustomer = new Customer();
        $targetCustomer->setFirstName('Too long first name for Account');
        $targetCustomer->setLastName('Too long last name for Account');

        $account = $this->provider->getAccount($targetCustomer);

        $this->assertInstanceOf(Account::class, $account);
        $this->assertEquals('Too long first name for Account Too long l', $account->getName());
    }

    public function testGetAccountUtf8()
    {
        $targetCustomer = new Customer();
        $targetCustomer->setFirstName('票驗驗後付票驗驗後付票驗驗後付票驗驗後付票驗驗後付票驗驗後付');
        $targetCustomer->setLastName('票驗驗後付票驗驗後付票驗驗後付票驗驗後付票驗驗後付票驗驗後付');

        $account = $this->provider->getAccount($targetCustomer);

        $this->assertInstanceOf(Account::class, $account);
        $this->assertEquals('票驗驗後付票驗驗後付票驗驗後付票驗驗後付票驗驗後付票驗驗後付 票驗驗後付票驗驗後付票', $account->getName());
    }

    public function testGetAccountNameComposition()
    {
        $targetCustomer = new Customer();
        $targetCustomer->setFirstName('First Name');
        $targetCustomer->setLastName('');

        $this->assertEquals('First Name', $this->provider->getAccount($targetCustomer)->getName());

        $targetCustomer->setFirstName('');
        $targetCustomer->setLastName('  Last Name  ');
        $this->assertEquals('Last Name', $this->provider->getAccount($targetCustomer)->getName());
    }

    /**
     * @return ManagerRegistry|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getDoctrineMock()
    {
        $metadata = $this->createMock(ClassMetadataInfo::class);
        $metadata->expects($this->any())
            ->method('getFieldMapping')
            ->with('name')
            ->willReturn(['length' => 42]);

        $manager = $this->createMock(ObjectManager::class);
        $manager->expects($this->any())
            ->method('getClassMetadata')
            ->with(Account::class)
            ->willReturn($metadata);

        $registry = $this->createMock(ManagerRegistry::class);
        $registry->expects($this->any())
            ->method('getManagerForClass')
            ->with(Account::class)
            ->willReturn($manager);

        return $registry;
    }
}
