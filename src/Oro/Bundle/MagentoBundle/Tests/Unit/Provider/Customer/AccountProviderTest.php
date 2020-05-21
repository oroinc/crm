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

class AccountProviderTest extends \PHPUnit\Framework\TestCase
{
    /** @var AutomaticDiscovery|\PHPUnit\Framework\MockObject\MockObject */
    private $automaticDiscovery;

    /** @var NewEntitiesHelper|\PHPUnit\Framework\MockObject\MockObject */
    private $newEntitiesHelper;

    /** @var AccountProvider */
    private $provider;

    protected function setUp(): void
    {
        $this->automaticDiscovery = $this->createMock(AutomaticDiscovery::class);
        $this->newEntitiesHelper = $this->createMock(NewEntitiesHelper::class);

        $this->provider = new AccountProvider(
            $this->newEntitiesHelper,
            $this->automaticDiscovery,
            $this->getDoctrineMock()
        );
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
     * @return ManagerRegistry|\PHPUnit\Framework\MockObject\MockObject
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
