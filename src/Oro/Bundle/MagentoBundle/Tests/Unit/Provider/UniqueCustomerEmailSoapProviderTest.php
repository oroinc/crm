<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\Provider;

use Oro\Bundle\MagentoBundle\Entity\Customer;
use Oro\Bundle\MagentoBundle\Entity\Store;
use Oro\Bundle\MagentoBundle\Provider\Transport\MagentoSoapTransportInterface;
use Oro\Bundle\MagentoBundle\Provider\UniqueCustomerEmailSoapProvider;

class UniqueCustomerEmailSoapProviderTest extends \PHPUnit\Framework\TestCase
{
    const ORIGIN_ID = 1;

    /**
     * @var UniqueCustomerEmailSoapProvider
     */
    protected $provider;

    protected function setUp(): void
    {
        $this->provider = new UniqueCustomerEmailSoapProvider();
    }

    /**
     * @dataProvider isCustomerHasUniqueEmaildataProvider
     *
     * @param array $customers
     * @param bool  $expected
     */
    public function testIsCustomerHasUniqueEmail(array $customers, $expected)
    {
        $store = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId'])
            ->getMock();

        $store->method('getId')->willReturn('1');

        $customer = $this->getMockBuilder(Customer::class)
            ->disableOriginalConstructor()
            ->setMethods(['getOriginId', 'getStore', 'getEmail'])
            ->getMock();

        $customer->method('getOriginId')->willReturn(self::ORIGIN_ID);
        $customer->method('getEmail')->willReturn('oro@mail.com');
        $customer->method('getStore')->willReturn($store);

        $transport = $this->createMock(MagentoSoapTransportInterface::class);

        $transport->method('call')->willReturn($customers);

        $result = $this->provider->isCustomerHasUniqueEmail($transport, $customer);
        $this->assertEquals($expected, $result);
    }

    /**
     * @return array
     */
    public function isCustomerHasUniqueEmaildataProvider()
    {
        return [
            'No customers' => [
                'customers' => [],
                'expected'  => true
            ],
            'Customer without id' => [
                'customers' => [
                    ['customer_id' => ''],
                ],
                'expected'  => false
            ],
            'Customer with same id as original customer' => [
                'customers' => [
                    ['customer_id' => self::ORIGIN_ID],
                ],
                'expected'  => true
            ],
            'Customer without customer_id' => [
                'customers' => [
                    [
                        'increment_id' => self::ORIGIN_ID
                    ],
                ],
                'expected'  => false
            ],
            'Several customers' => [
                'customers' => [
                    ['customer_id' => self::ORIGIN_ID],
                    ['customer_id' => 5],
                ],
                'expected'  => false
            ],
        ];
    }
}
