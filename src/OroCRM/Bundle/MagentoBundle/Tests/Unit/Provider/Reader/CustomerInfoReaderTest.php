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

    public function testRead()
    {
        $originId = uniqid();
        $expectedData = new Customer();
        $expectedData->setOriginId($originId);

        $this->context->expects($this->any())
            ->method('getConfiguration')
            ->will($this->returnValue(['data' => $expectedData]));

        $this->transport->expects($this->once())
            ->method('getCustomerInfo')
            ->will(
                $this->returnCallback(
                    function (Customer $customer) {
                        $object = new \stdClass();
                        $object->origin_id = $customer->getOriginId();

                        return $object;
                    }
                )
            );

        $address = new \stdClass();
        $address->zip = uniqid();
        $this->transport->expects($this->once())
            ->method('getCustomerAddresses')
            ->will($this->returnValue([$address]));

        $this->transport->expects($this->once())
            ->method('getDependencies')
            ->will($this->returnValue([]));

        $reader = $this->getReader();
        $reader->setStepExecution($this->stepExecutionMock);

        $this->assertEquals(
            [
                'origin_id' => $originId,
                'addresses' => [
                    ['zip' => $address->zip]
                ],
                'group' => ['originId' => null],
                'store' => ['originId' => null],
                'website' => ['originId' => null]
            ],
            $reader->read()
        );
        $this->assertNull($reader->read());
    }
}
