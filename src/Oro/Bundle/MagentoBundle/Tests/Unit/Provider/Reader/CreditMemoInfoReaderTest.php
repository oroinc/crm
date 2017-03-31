<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\Provider\Reader;

use Oro\Bundle\MagentoBundle\Entity\CreditMemo;
use Oro\Bundle\MagentoBundle\Provider\Reader\CreditMemoInfoReader;

class CreditMemoInfoReaderTest extends AbstractInfoReaderTest
{
    /**
     * @return CreditMemoInfoReader
     */
    protected function getReader()
    {
        $reader = new CreditMemoInfoReader($this->contextRegistry, $this->logger, $this->contextMediator);
        $reader->setContextKey('creditMemoIds');

        return $reader;
    }

    public function testRead()
    {
        $data = ['creditMemoIds' => [321]];
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
        $expectedData = new CreditMemo();
        $expectedData->setIncrementId($originId);

        $this->context->expects($this->any())
            ->method('getConfiguration')
            ->will($this->returnValue(['data' => $expectedData]));

        $this->transport->expects($this->once())
            ->method('getCreditMemoInfo')
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
}
