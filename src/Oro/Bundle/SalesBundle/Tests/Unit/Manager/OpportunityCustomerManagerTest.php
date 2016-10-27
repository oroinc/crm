<?php

namespace Oro\Bundle\SalesBundle\Tests\Unit\Manager;

use Symfony\Component\PropertyAccess\PropertyAccess;

use Oro\Bundle\EntityConfigBundle\Config\Config;
use Oro\Bundle\SalesBundle\Entity\Lead;
use Oro\Bundle\SalesBundle\Entity\Opportunity as EntityOpportunity;
use Oro\Bundle\SalesBundle\Manager\OpportunityCustomerManager;
use Oro\Bundle\SalesBundle\Tests\Unit\Stub\Customer1;
use Oro\Bundle\SalesBundle\Tests\Unit\Stub\Customer2;
use Oro\Bundle\SalesBundle\Tests\Unit\Stub\Opportunity;

class OpportunityCustomerManagerTest extends \PHPUnit_Framework_TestCase
{
    /** @var OpportunityCustomerManager */
    protected $opportunityCustomerManager;

    public function setUp()
    {
        $salesConfigProvider = $this->getMockBuilder('Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider')
            ->disableOriginalConstructor()
            ->getMock();
        $salesConfigProvider->expects($this->any())
            ->method('getConfig')
            ->will($this->returnValueMap([
                [
                    EntityOpportunity::class, null,
                    new Config(
                        $this->getMock('Oro\Bundle\EntityConfigBundle\Config\Id\ConfigIdInterface'),
                        [
                            'customers' => [
                                'Oro\Bundle\SalesBundle\Tests\Unit\Stub\Customer1' => 'customer1',
                                'Oro\Bundle\SalesBundle\Tests\Unit\Stub\Customer2' => 'customer2',
                            ],
                        ]
                    ),
                ],
            ]));

        $this->opportunityCustomerManager = new OpportunityCustomerManager(
            PropertyAccess::createPropertyAccessor(),
            $salesConfigProvider
        );
    }

    /**
     * @dataProvider getterAndSetterProvider
     */
    public function testGetterAndSetter(Opportunity $opportunity, $customer)
    {
        $this->opportunityCustomerManager->setCustomer($opportunity, $customer);
        $this->assertSame(
            $customer,
            $this->opportunityCustomerManager->getCustomer($opportunity, $customer)
        );
    }

    public function getterAndSetterProvider()
    {
        return [
            [
                new Opportunity(),
                new Customer1(),
            ],
            [
                new Opportunity(),
                new Customer2(),
            ],
            [
                (new Opportunity())
                    ->setCustomer1(new Customer1()),
                new Customer2(),
            ],
            [
                (new Opportunity())
                    ->setCustomer2(new Customer2()),
                new Customer1(),
            ],
            [
                new Opportunity(),
                null,
            ],
            [
                (new Opportunity())
                    ->setCustomer1(new Customer1()),
                null,
            ],
            [
                (new Opportunity())
                    ->setCustomer2(new Customer2()),
                null,
            ],
        ];
    }

    /**
     * @dataProvider hasCustomerProvider
     */
    public function testHasCustomer(Opportunity $opportunity, $customer, $expectedValue)
    {
        $this->assertSame(
            $expectedValue,
            $this->opportunityCustomerManager->hasCustomer($opportunity, $customer)
        );
    }

    public function hasCustomerProvider()
    {
        $customer1 = new Customer1();
        $customer2 = new Customer2();

        return [
            [
                (new Opportunity())
                    ->setCustomer1($customer1),
                $customer1,
                true,
            ],
            [
                (new Opportunity())
                    ->setCustomer2($customer2),
                $customer2,
                true,
            ],
            [
                (new Opportunity())
                    ->setCustomer1($customer1),
                $customer2,
                false,
            ],
            [
                (new Opportunity())
                    ->setCustomer2($customer2),
                $customer1,
                false,
            ],
            [
                new Opportunity(),
                $customer1,
                false,
            ],
            [
                new Opportunity(),
                $customer2,
                false,
            ],
        ];
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Customer is expected to be instance of one of the
     */
    public function testHasCustomerThrowsExceptionOnInvalidCustomer()
    {
        $this->opportunityCustomerManager->hasCustomer(new Opportunity(), new Lead());
    }
}
