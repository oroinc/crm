<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\Provider\Reader;

use Oro\Bundle\MagentoBundle\Entity\Customer;
use Oro\Bundle\MagentoBundle\Provider\Reader\CustomerInfoReader;

class CustomerInfoReaderTest extends AbstractInfoReaderTest
{
    /**
     * @return CustomerInfoReader
     */
    protected function getReader()
    {
        $reader = new CustomerInfoReader($this->contextRegistry, $this->logger, $this->contextMediator);
        $reader->setContextKey('customerIds');

        return $reader;
    }

    /**
     * @param array $data
     *
     * @dataProvider dataProvider
     */
    public function testRead(array $data)
    {
        $this->executionContext->expects($this->once())
            ->method('get')
            ->will(
                $this->returnCallback(
                    function ($key) use ($data) {
                        if (empty($data[$key])) {
                            return null;
                        }

                        return $data[$key];
                    }
                )
            );

        $originId = 123;
        $expectedData = new Customer();
        $expectedData->setOriginId($originId);

        $this->context->expects($this->any())
            ->method('getConfiguration')
            ->will($this->returnValue(['data' => $expectedData]));

        $this->transport->expects($this->once())
            ->method('getCustomerInfo')
            ->will(
                $this->returnCallback(
                    function ($customerId) {
                        return [
                            'origin_id' => $customerId,
                            'group_id' => 0,
                            'store_id' => 0,
                            'website_id' => 0
                        ];
                    }
                )
            );

        $address = ['zip' => uniqid()];
        $this->transport->expects($this->once())
            ->method('getCustomerAddresses')
            ->will($this->returnValue([$address]));

        $reader = $this->getReader();
        $reader->setStepExecution($this->stepExecutionMock);

        $this->assertEquals(
            [
                'origin_id' => $originId,
                'group_id' => 0,
                'store_id' => 0,
                'website_id' => 0,
                'addresses' => [
                    ['zip' => $address['zip']]
                ]
            ],
            $reader->read()
        );
        $this->assertNull($reader->read());
    }

    /**
     * {@inheritdoc}
     */
    public function dataProvider()
    {
        return [
            [
                [
                    'customerIds' => [123],
                    'orderIds' => [321]
                ]
            ]
        ];
    }
}
