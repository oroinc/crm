<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Unit\Provider\Reader;

use OroCRM\Bundle\MagentoBundle\Entity\Customer;
use OroCRM\Bundle\MagentoBundle\Provider\Reader\CustomerInfoReader;

class CustomerInfoReaderTest extends AbstractInfoReaderTest
{
    /**
     * @return CustomerInfoReader
     */
    protected function getReader()
    {
        $reader = new CustomerInfoReader($this->contextRegistry, $this->logger, $this->contextMediator);
        $reader->setClassName('OroCRM\Bundle\MagentoBundle\Entity\Customer');

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
            ->will($this->returnValue($data));

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
                        $object = new \stdClass();
                        $object->origin_id = $customerId;
                        $object->group_id = 0;
                        $object->store_id = 0;
                        $object->website_id = 0;

                        return $object;
                    }
                )
            );

        $address = new \stdClass();
        $address->zip = uniqid();
        $this->transport->expects($this->once())
            ->method('getCustomerAddresses')
            ->will($this->returnValue([$address]));

        $this->transport->expects($this->atLeastOnce())
            ->method('getDependencies')
            ->will(
                $this->returnValue(
                    [
                        'groups' => [['customer_group_id' => $originId]],
                        'websites' => [['id' => $originId]],
                        'stores' => [['website_id' => $originId]]
                    ]
                )
            );

        $reader = $this->getReader();
        $reader->setStepExecution($this->stepExecutionMock);

        $this->assertEquals(
            [
                'origin_id' => $originId,
                'group_id' => 0,
                'store_id' => 0,
                'website_id' => 0,
                'addresses' => [
                    ['zip' => $address->zip]
                ],
                'group' => ['customer_group_id' => $originId, 'originId' => $originId],
                'store' => ['website_id' => $originId, 'originId' => 0],
                'website' => ['id' => $originId, 'originId' => $originId]
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
                    'OroCRM\Bundle\MagentoBundle\Entity\Customer' => [123],
                    'OroCRM\Bundle\MagentoBundle\Entity\Order' => [321]
                ]
            ]
        ];
    }
}
