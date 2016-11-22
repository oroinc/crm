<?php

namespace Oro\Bundle\SalesBundle\Tests\Unit\Form\DataTransformer;

use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\SalesBundle\Form\DataTransformer\CustomerToStringTransformer;
use Oro\Bundle\SalesBundle\Provider\Customer\ConfigProvider;
use Oro\Bundle\SalesBundle\Tests\Unit\Fixture\CustomerStub as Customer;

class CustomerToStringTransformerTest extends \PHPUnit_Framework_TestCase
{
    /** @var CustomerToStringTransformer */
    protected $customerToStringTransformer;

    /** @var ConfigProvider|\PHPUnit_Framework_MockObject_MockObject */
    protected $configProvider;

    public function setUp()
    {
        $entityToStringTransformer = $this->getMock('Symfony\Component\Form\DataTransformerInterface');
        $entityToStringTransformer->expects($this->any())
            ->method('transform')
            ->will($this->returnValue('parentTransform'));
        $entityToStringTransformer->expects($this->any())
            ->method('reverseTransform')
            ->will($this->returnCallback(function ($value) {
                $decoded = json_decode($value, true);

                if (isset($decoded['value'])) {
                    return (new Account())->setName($decoded['value']);
                }

                $entity       = new $decoded['entityClass'];
                $accountIdRef = new \ReflectionProperty($decoded['entityClass'], 'id');
                $accountIdRef->setAccessible(true);
                $accountIdRef->setValue($entity, 1);

                return $entity;
            }));

        $em = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();

        $customerRepository = $this->getMockBuilder(\Doctrine\ORM\EntityRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $customerRepository->expects($this->any())
            ->method('findOneBy')
            ->will($this->returnValue(null));

        $doctrineHelper = $this->getMockBuilder('Oro\Bundle\EntityBundle\ORM\DoctrineHelper')
            ->disableOriginalConstructor()
            ->getMock();
        $doctrineHelper->expects($this->any())
            ->method('getEntityManager')
            ->with($this->isInstanceOf(Account::class))
            ->will($this->returnValue($em));
        $doctrineHelper->expects($this->any())
            ->method('getEntityRepository')
            ->with('Oro\Bundle\SalesBundle\Entity\Customer')
            ->will($this->returnValue($customerRepository));
        $this->configProvider = $this
            ->getMockBuilder('Oro\Bundle\SalesBundle\Provider\Customer\ConfigProvider')
            ->disableOriginalConstructor()
            ->setMethods(['getCustomerClasses'])
            ->getMock();
        $this->configProvider
            ->expects($this->any())
            ->method('getCustomerClasses')
            ->willReturn([]);


        $this->customerToStringTransformer = $this->getMockBuilder(CustomerToStringTransformer::class)
            ->setMethods(['createCustomer'])
            ->setConstructorArgs([
                $entityToStringTransformer,
                $doctrineHelper,
                $this->configProvider
            ])
            ->getMock();
        $this->customerToStringTransformer->expects($this->any())
            ->method('createCustomer')
            ->will($this->returnCallback(function () {
                return new Customer();
            }));
    }

    /**
     * @dataProvider reverseTransformProvider
     */
    public function testReverseTransform($value, $expectedValue)
    {
        $this->assertEquals(
            $expectedValue,
            $this->customerToStringTransformer->reverseTransform($value)
        );
    }

    public function reverseTransformProvider()
    {
        $accountIdRef = new \ReflectionProperty(Account::class, 'id');
        $accountIdRef->setAccessible(true);
        $existingAccount = new Account();
        $accountIdRef->setValue($existingAccount, 1);

        return [
            'new account'      => [
                json_encode(['value' => 'new account']),
                (new Customer())
                    ->setTarget(
                        (new Account())
                            ->setName('new account')
                    )
            ],
            'existing account' => [
                json_encode(['entityClass' => Account::class, 'entityId' => 1]),
                (new Customer())
                    ->setTarget($existingAccount),
            ],
        ];
    }

    /**
     * @dataProvider transformProvider
     */
    public function testTransform($value, $expectedValue)
    {
        $this->assertEquals(
            $expectedValue,
            $this->customerToStringTransformer->transform($value)
        );
    }

    public function transformProvider()
    {
        $accountIdRef = new \ReflectionProperty(Account::class, 'id');
        $accountIdRef->setAccessible(true);
        $existingAccount = (new Account())
            ->setName('existing account');
        $accountIdRef->setValue($existingAccount, 1);

        return [
            'new account'      => [
                (new Customer())
                    ->setTarget(
                        (new Account())
                            ->setName('new account')
                    ),
                json_encode(['value' => 'new account']),
            ],
            'existing account' => [
                (new Customer())->setTarget($existingAccount),
                'parentTransform',
            ],
        ];
    }
}
