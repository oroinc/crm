<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\Provider\Reader;

use Oro\Bundle\MagentoBundle\Entity\Order;
use Oro\Bundle\MagentoBundle\Provider\Reader\OrderInfoReader;

class OrderInfoReaderTest extends AbstractInfoReaderTest
{
    /**
     * @return OrderInfoReader
     */
    protected function getReader()
    {
        $reader = new OrderInfoReader($this->contextRegistry, $this->logger, $this->contextMediator);
        $reader->setContextKey('orderIds');

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

        $originId = 321;
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

        $reader = $this->getReader();
        $reader->setStepExecution($this->stepExecutionMock);

        $this->assertEquals(
            [
                'origin_id' => $originId,
                'store_id' => 0
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
