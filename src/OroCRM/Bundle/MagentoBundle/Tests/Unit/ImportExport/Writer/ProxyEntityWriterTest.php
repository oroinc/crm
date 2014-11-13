<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Unit\Importexport\Writer;

use Akeneo\Bundle\BatchBundle\Item\ItemWriterInterface;

use OroCRM\Bundle\MagentoBundle\Entity\Cart;
use OroCRM\Bundle\MagentoBundle\Entity\Customer;
use OroCRM\Bundle\MagentoBundle\Entity\Order;
use OroCRM\Bundle\MagentoBundle\ImportExport\Writer\ProxyEntityWriter;

class ProxyEntityWriterTest extends \PHPUnit_Framework_TestCase
{
    /** @var ItemWriterInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $wrapped;

    /** @var ProxyEntityWriter */
    protected $writer;

    protected function setUp()
    {
        $this->wrapped = $this->getMock('Akeneo\Bundle\BatchBundle\Item\ItemWriterInterface');
        $this->writer  = new ProxyEntityWriter($this->wrapped);
    }

    protected function tearDown()
    {
        unset($this->writer, $this->wrapped);
    }

    /**
     * @dataProvider itemsProvider
     *
     * @param array $items
     * @param array $expectedItems
     */
    public function testWrite(array $items, array $expectedItems)
    {
        $this->wrapped->expects($this->once())->method('write')
            ->with($this->equalTo($expectedItems));

        $this->writer->write($items);
    }

    /**
     * @return array
     */
    public function itemsProvider()
    {
        $order1 = new Order();
        $order2 = new Order();
        $order1->setIncrementId('1111');
        $order2->setIncrementId('2222');
        $order3 = clone $order1;

        $cart1 = new Cart();
        $cart2 = new Cart();
        $cart1->setOriginId(1111);
        $cart2->setOriginId(2222);
        $cart3 = clone $cart1;

        $customer1 = new Customer();
        $customer1->setOriginId(111);
        $customer2 = clone $customer1;

        $someEntity  = new \stdClass();
        $someEntity2 = new \stdClass();

        return [
            'should skip non-unique orders'                                  => [
                '$items'         => [$order1, $order2, $order3],
                '$expectedItems' => [$order3->getIncrementId() => $order3, $order2->getIncrementId() => $order2]
            ],
            'should skip non-unique carts'                                   => [
                '$items'         => [$cart1, $cart2, $cart3],
                '$expectedItems' => [$cart3->getOriginId() => $cart3, $cart2->getOriginId() => $cart2]
            ],
            'should skip non-unique customers'                               => [
                '$items'         => [$customer1, $customer2],
                '$expectedItems' => [$customer2->getOriginId() => $customer2]
            ],
            'should not break logic with entities that not consist originId' => [
                '$items'         => [$someEntity, $someEntity2],
                '$expectedItems' => [$someEntity, $someEntity2]
            ]
        ];
    }

    public function testSetStepExecutionSetToWrappedWriter()
    {
        $wrapped       = $this->getMock('OroCRM\Bundle\MagentoBundle\Tests\Unit\Stub\StepExecutionAwareWriter');
        $writer        = new ProxyEntityWriter($wrapped);
        $stepExecution = $this->getMockBuilder('Akeneo\Bundle\BatchBundle\Entity\StepExecution')
            ->disableOriginalConstructor()->getMock();
        $wrapped->expects($this->once())->method('setStepExecution')
            ->with($this->equalTo($stepExecution));

        $writer->setStepExecution($stepExecution);
    }

    public function testSetStepExecutionDoesNotProvokeErrorWithRegularWriter()
    {
        $stepExecution = $this->getMockBuilder('Akeneo\Bundle\BatchBundle\Entity\StepExecution')
            ->disableOriginalConstructor()->getMock();

        $this->writer->setStepExecution($stepExecution);
    }
}
