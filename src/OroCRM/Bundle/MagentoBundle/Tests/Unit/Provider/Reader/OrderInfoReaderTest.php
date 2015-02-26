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
                        $object->store_id = 0;

                        return $object;
                    }
                )
            );

        $this->transport->expects($this->once())
            ->method('getDependencies')
            ->will(
                $this->returnValue(
                    [
                        'groups' => [['customer_group_id' => $originId]],
                        'websites' => [$originId => ['id' => $originId, 'code' => 'code', 'name' => 'name']],
                        'stores' => [['website_id' => $originId, 'code' => 'code', 'name' => 'name']],
                    ]
                )
            );

        $reader = $this->getReader();
        $reader->setStepExecution($this->stepExecutionMock);

        $this->assertEquals(
            [
                'origin_id' => $originId,
                'store_id' => 0,
                'store_code' => 'code',
                'store_storename' => 'name',
                'store_website_id' => $originId,
                'store_website_code' => 'code',
                'store_website_name' => 'name',
                'payment_method' => null
            ],
            $reader->read()
        );
        $this->assertNull($reader->read());
    }
}
