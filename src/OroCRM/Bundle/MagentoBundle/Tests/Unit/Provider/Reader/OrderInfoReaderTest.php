<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Unit\Provider\Reader;

use OroCRM\Bundle\MagentoBundle\Entity\Order;
use OroCRM\Bundle\MagentoBundle\Provider\Reader\OrderInfoReader;

class OrderInfoReaderTest extends AbstractInfoReaderTest
{
    /**
     * @return OrderInfoReader
     */
    protected function getReader()
    {
        $reader = new OrderInfoReader($this->contextRegistry, $this->logger, $this->contextMediator);
        $reader->setClassName('OroCRM\Bundle\MagentoBundle\Entity\Order');

        return $reader;
    }

    public function testRead()
    {
        $originId = uniqid();
        $expectedData = new Order();
        $expectedData->setIncrementId($originId);

        $this->context->expects($this->any())
            ->method('getConfiguration')
            ->will($this->returnValue(['data' => $expectedData]));

        $this->transport->expects($this->once())
            ->method('getOrderInfo')
            ->will(
                $this->returnCallback(
                    function ($incrementId) {
                        $object = new \stdClass();
                        $object->origin_id = $incrementId;

                        return $object;
                    }
                )
            );

        $this->transport->expects($this->once())
            ->method('getDependencies')
            ->will($this->returnValue([]));

        $reader = $this->getReader();
        $reader->setStepExecution($this->stepExecutionMock);

        $this->assertEquals(
            [
                'origin_id' => $originId,
                'store_code' => null,
                'store_storename' => null,
                'store_website_id' => null,
                'store_website_code' => null,
                'store_website_name' => null,
                'payment_method' => null
            ],
            $reader->read()
        );
        $this->assertNull($reader->read());
    }
}
