<?php

namespace Oro\Bundle\SalesBundle\Tests\Unit\Api;

use Oro\Bundle\ApiBundle\Provider\ResourcesProvider;
use Oro\Bundle\ApiBundle\Request\DataType;
use Oro\Bundle\ApiBundle\Request\RequestType;
use Oro\Bundle\ApiBundle\Request\ValueNormalizer;
use Oro\Bundle\SalesBundle\Api\AccountCustomerAssociationProvider;
use Oro\Bundle\SalesBundle\Entity\Manager\AccountCustomerManager;
use Oro\Bundle\SalesBundle\Provider\Customer\ConfigProvider;

class AccountCustomerAssociationProviderTest extends \PHPUnit\Framework\TestCase
{
    /** @var ConfigProvider|\PHPUnit\Framework\MockObject\MockObject */
    private $configProvider;

    /** @var ValueNormalizer|\PHPUnit\Framework\MockObject\MockObject */
    private $valueNormalizer;

    /** @var ResourcesProvider|\PHPUnit\Framework\MockObject\MockObject */
    private $resourcesProvider;

    /** @var AccountCustomerAssociationProvider */
    private $accountCustomerAssociationProvider;

    protected function setUp(): void
    {
        $this->configProvider = $this->createMock(ConfigProvider::class);
        $this->valueNormalizer = $this->createMock(ValueNormalizer::class);
        $this->resourcesProvider = $this->createMock(ResourcesProvider::class);

        $this->accountCustomerAssociationProvider = new AccountCustomerAssociationProvider(
            ['Test\AnotherCustomer' => 'renamedCustomers'],
            $this->configProvider,
            $this->valueNormalizer,
            $this->resourcesProvider
        );
    }

    /**
     * @dataProvider isCustomerEntityDataProvider
     */
    public function testIsCustomerEntity(bool $isCustomerEntity): void
    {
        $entityClass = 'Test\Entity';

        $this->configProvider->expects(self::once())
            ->method('isCustomerClass')
            ->with($entityClass)
            ->willReturn($isCustomerEntity);

        self::assertSame(
            $isCustomerEntity,
            $this->accountCustomerAssociationProvider->isCustomerEntity($entityClass)
        );
    }

    public function isCustomerEntityDataProvider(): array
    {
        return [
            [false],
            [true]
        ];
    }

    public function testGetAccountCustomerAssociations(): void
    {
        $version = 'latest';
        $requestType = new RequestType([RequestType::REST]);

        $this->configProvider->expects(self::once())
            ->method('getCustomerClasses')
            ->willReturn(['Test\Customer', 'Test\AnotherCustomer', 'Test\NotAccessibleCustomer']);
        $this->resourcesProvider->expects(self::exactly(3))
            ->method('isResourceAccessible')
            ->willReturnMap([
                ['Test\Customer', $version, $requestType, true],
                ['Test\AnotherCustomer', $version, $requestType, true],
                ['Test\NotAccessibleCustomer', $version, $requestType, false],
            ]);
        $this->valueNormalizer->expects(self::once())
            ->method('normalizeValue')
            ->with('Test\Customer', DataType::ENTITY_TYPE, $requestType)
            ->willReturn('customers');

        $expected = [
            'customers'        => [
                'className'       => 'Test\Customer',
                'associationName' => AccountCustomerManager::getCustomerTargetField('Test\Customer')
            ],
            'renamedCustomers' => [
                'className'       => 'Test\AnotherCustomer',
                'associationName' => AccountCustomerManager::getCustomerTargetField('Test\AnotherCustomer')
            ]
        ];

        self::assertSame(
            $expected,
            $this->accountCustomerAssociationProvider->getAccountCustomerAssociations($version, $requestType)
        );
        // test memory cache
        self::assertSame(
            $expected,
            $this->accountCustomerAssociationProvider->getAccountCustomerAssociations($version, $requestType)
        );
    }

    public function testGetCustomerTargetAssociationName(): void
    {
        $customerEntityClass = 'Test\Entity';

        self::assertEquals(
            AccountCustomerManager::getCustomerTargetField($customerEntityClass),
            $this->accountCustomerAssociationProvider->getCustomerTargetAssociationName($customerEntityClass)
        );
    }
}
