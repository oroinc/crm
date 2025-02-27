<?php

namespace Oro\Bundle\SalesBundle\Tests\Functional\EntityExtend;

use Oro\Bundle\EntityExtendBundle\Tests\Functional\EntityExtend\Extension\EntityExtendTransportTrait;
use Oro\Bundle\SalesBundle\Entity\Customer;
use Oro\Bundle\SalesBundle\EntityExtend\CustomerEntityFieldExtension;
use Oro\Bundle\TestFrameworkBundle\Entity\TestExtendedEntity;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

/**
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class CustomerEntityFieldExtensionTest extends WebTestCase
{
    use EntityExtendTransportTrait;

    private CustomerEntityFieldExtension $customerEntityFieldExtension;

    public function setUp(): void
    {
        self::bootKernel();
        $this->customerEntityFieldExtension = new CustomerEntityFieldExtension();
    }

    /**
     * @dataProvider getMethodsDataProvider
     */
    public function testGetMethodsIsApplicable(string $class, array $expectedMethods, array $unexpectedMethods): void
    {
        $transport = $this->createTransport(Customer::class);
        foreach ($expectedMethods as $expectedMethod) {
            $transport->setName($expectedMethod);
            $methods = $this->customerEntityFieldExtension->getMethods($transport);

            self::assertContains($expectedMethod, $methods);
        }
        // unexpected methods
        foreach ($unexpectedMethods as $unexpectedMethod) {
            $transport->setName($unexpectedMethod);
            $methods = $this->customerEntityFieldExtension->getMethods($transport);

            self::assertNotContains($unexpectedMethod, $methods);
        }
    }

    public function getMethodsDataProvider(): array
    {
        return [
            'get many_to_one methods' => [
                'class' => Customer::class,
                'expectedMethods' => [
                    'supportCustomerTarget',
                    'getCustomerTarget',
                    'setCustomerTarget'
                ],
                'unexpectedMethods' => []
            ],
            'get with unexpected methods check' => [
                'class' => Customer::class,
                'expectedMethods' => [
                    'supportCustomerTarget',
                    'getCustomerTarget',
                    'setCustomerTarget'
                ],
                'unexpectedMethods' => [
                    'hasCustomerTarget',
                    'addCustomerTarget',
                    'removeCustomerTarget'
                ]
            ],
        ];
    }

    public function testGetMethodsIsNotApplicable(): void
    {
        // not applicable entity set
        $transport = $this->createTransport(TestExtendedEntity::class);
        $methods = $this->customerEntityFieldExtension->getMethods($transport);

        self::assertSame([], $methods);
    }

    public function testGetIsNotApplicable(): void
    {
        // not applicable entity set
        $transport = $this->createTransport(new TestExtendedEntity());
        $transport->setName('target');
        $this->customerEntityFieldExtension->get($transport);

        self::assertFalse($transport->isProcessed());
    }

    public function testGetIsNotTargetPropertyName(): void
    {
        $transport = $this->createTransport(new Customer());
        $transport->setName('not_target_property');
        $this->customerEntityFieldExtension->get($transport);

        self::assertFalse($transport->isProcessed());
    }

    public function testSetIsNotApplicable(): void
    {
        // not applicable entity
        $transport = $this->createTransport(new TestExtendedEntity());
        $transport->setName('target');
        $transport->setValue(null);
        $this->customerEntityFieldExtension->set($transport);

        self::assertFalse($transport->isProcessed());
    }

    public function testSetNotTarget(): void
    {
        $transport = $this->createTransport(new Customer());
        $transport->setName('not_target_property');
        $transport->setValue(null);
        $this->customerEntityFieldExtension->set($transport);

        self::assertFalse($transport->isProcessed());
    }

    /**
     * @dataProvider callDataProvider
     */
    public function testCallIsApplicable(
        string|object $classOrObject,
        string $name,
        bool $isProcessed,
        array $arguments,
        mixed $value
    ): void {
        $transport = $this->createTransport($classOrObject);
        $transport->setName($name);
        $transport->setValue($value);
        $transport->setArguments($arguments);
        $this->customerEntityFieldExtension->call($transport);

        self::assertSame($isProcessed, $transport->isProcessed());
    }

    public function callDataProvider(): array
    {
        return [
            'support customer target call' => [
                'classOrObject' => new Customer(),
                'name' => 'supportCustomerTarget',
                'isProcessed' => true,
                'arguments' => [Customer::class],
                'value' => null,
            ],
            'get customer target call' => [
                'classOrObject' => new Customer(),
                'name' => 'getCustomerTarget',
                'isProcessed' => true,
                'arguments' => [],
                'value' => null,
            ],
            'set customer target call' => [
                'classOrObject' => new Customer(),
                'name' => 'setCustomerTarget',
                'isProcessed' => true,
                'arguments' => [],
                'value' => null,
            ],
        ];
    }

    public function testPropertyExistsIsNotApplicable(): void
    {
        $transport = $this->createTransport(new TestExtendedEntity());
        $transport->setName('target');
        $this->customerEntityFieldExtension->propertyExists($transport);

        self::assertFalse($transport->isProcessed());
        self::assertNull($transport->getResult());
    }

    public function testPropertyExistsIsNotTarget(): void
    {
        $transport = $this->createTransport(new TestExtendedEntity());
        $transport->setName('undefined_property');
        $this->customerEntityFieldExtension->propertyExists($transport);

        self::assertFalse($transport->isProcessed());
        self::assertNull($transport->getResult());
    }

    public function testMethodExistsApplicable(): void
    {
        $methods = [
            'supportCustomerTarget',
            'getCustomerTarget',
            'setCustomerTarget'
        ];
        foreach ($methods as $method) {
            $transport = $this->createTransport(new Customer());
            $transport->setName($method);
            $this->customerEntityFieldExtension->methodExists($transport);

            self::assertTrue($transport->isProcessed());
        }
    }

    public function testMethodExistsIsNotApplicable(): void
    {
        // not applicable entity set
        $transport = $this->createTransport(new TestExtendedEntity());
        $transport->setName('getAssociationRelationKind');
        $this->customerEntityFieldExtension->methodExists($transport);

        self::assertFalse($transport->isProcessed());
        self::assertNull($transport->getResult());
    }

    public function testGetMethodInfoIsNotApplicable(): void
    {
        // not applicable entity set
        $transport = $this->createTransport(new TestExtendedEntity());
        $transport->setName('getAssociationRelationKind');
        $this->customerEntityFieldExtension->getMethodInfo($transport);

        self::assertFalse($transport->isProcessed());
        self::assertNull($transport->getResult());
    }

    /**
     * @dataProvider getMethodInfoDataProvider
     */
    public function testGetMethodInfoIsApplicable(
        string|object $classOrObject,
        string $name,
        bool $isProcessed,
        array $info
    ): void {
        $transport = $this->createTransport($classOrObject);
        $transport->setName($name);
        $this->customerEntityFieldExtension->getMethodInfo($transport);

        self::assertSame($isProcessed, $transport->isProcessed());
        self::assertSame($info, $transport->getResult());
    }


    public function getMethodInfoDataProvider(): array
    {
        return [
            'support customer target info' => [
                'classOrObject' => new Customer(),
                'name' => 'supportCustomerTarget',
                'isProcessed' => true,
                'info' => [
                    'fieldName' => 'customer',
                    'fieldType' => 'bool',
                    'is_extend' => true,
                    'is_nullable' => false,
                    'is_serialized' => false
                ],
            ],
            'get customer target info' => [
                'classOrObject' => new Customer(),
                'name' => 'getCustomerTarget',
                'isProcessed' => true,
                'info' => [
                    'fieldName' => 'customer',
                    'fieldType' => '?object',
                    'is_extend' => true,
                    'is_nullable' => true,
                    'is_serialized' => false
                ],
            ],
            'set customer target call' => [
                'classOrObject' => new Customer(),
                'name' => 'setCustomerTarget',
                'isProcessed' => true,
                'info' => [
                    'fieldName' => 'customer',
                    'fieldType' => 'self',
                    'is_extend' => true,
                    'is_nullable' => false,
                    'is_serialized' => false
                ],
            ],
        ];
    }
}
