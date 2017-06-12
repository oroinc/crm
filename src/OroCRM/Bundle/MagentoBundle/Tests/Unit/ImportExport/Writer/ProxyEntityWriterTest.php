<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Unit\ImportExport\Writer;

use Akeneo\Bundle\BatchBundle\Item\ItemWriterInterface;

use Oro\Bundle\ImportExportBundle\Field\DatabaseHelper;

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

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|DatabaseHelper
     */
    protected $databaseHelper;

    protected function setUp()
    {
        $this->wrapped = $this
            ->getMockBuilder('Akeneo\Bundle\BatchBundle\Item\ItemWriterInterface')
            ->setMethods(['write'])
            ->getMock();

        $this->databaseHelper = $this->getMockBuilder('Oro\Bundle\ImportExportBundle\Field\DatabaseHelper')
            ->disableOriginalConstructor()
            ->getMock();

        $this->writer  = new ProxyEntityWriter($this->wrapped, $this->databaseHelper);
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

        $stepExecution = $this->getMockBuilder('Akeneo\Bundle\BatchBundle\Entity\StepExecution')
            ->disableOriginalConstructor()->getMock();
        $this->databaseHelper->expects($this->once())->method('onClear');
        $this->writer->setStepExecution($stepExecution);

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

        $customerGuest1 = new Customer();
        $customerGuest2 = new Customer();

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
            'dont skip guest customers'                                      => [
                '$items'         => [$customerGuest1, $customerGuest1, $customerGuest2],
                '$expectedItems' => [
                    spl_object_hash($customerGuest1) => $customerGuest1,
                    spl_object_hash($customerGuest2) => $customerGuest2,
                ],
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
        $writer        = new ProxyEntityWriter($wrapped, $this->databaseHelper);
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

    /**
     * @param $actualCustomersArray
     * @param $expectedCustomersArray
     *
     * @dataProvider customerProvider
     */
    public function testMergeGuestCustomers($actualCustomersArray, $expectedCustomersArray)
    {
        $this->wrapped
            ->expects($this->once())
            ->method('write')
            ->with($expectedCustomersArray);

        $this->writer->write($actualCustomersArray);
    }

    /**
     * @param string $customerEmail
     * @param int $channelId
     * @param bool $isGuest
     * @param int $originId
     *
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    public function getCustomer($customerEmail, $channelId, $isGuest = true, $originId = null)
    {
        $channel = $this
            ->getMockBuilder('OroCRM\Bundle\ChannelBundle\Entity\Channel')
            ->setMethods(['getId'])
            ->disableOriginalConstructor()
            ->getMock();
        $customer = $this
            ->getMockBuilder('OroCRM\Bundle\MagentoBundle\Entity\Customer')
            ->setMethods(['getChannel', 'getOriginId'])
            ->disableOriginalConstructor()
            ->getMock();
        if ($channelId) {
            $channel
                ->expects($this->any())
                ->method('getId')
                ->will($this->returnValue($channelId));
            $customer
                ->expects($this->any())
                ->method('getChannel')
                ->will($this->returnValue($channel));
        }

        if (!$isGuest && $originId) {
            $customer
                ->expects($this->any())
                ->method('getOriginId')
                ->will($this->returnValue($originId));
        }

        $customer->setEmail($customerEmail);
        $customer->setGuest($isGuest);

        return $customer;
    }

    /**
     * @return array
     */
    public function customerProvider()
    {
        return [
            [ //guest mode. customers will be merged
                [$this->getCustomer('test@test.com', 1), $this->getCustomer('test@test.com', 1)],
                [$this->getUniqueHash('test@test.com', 1) => $this->getCustomer('test@test.com', 1)]
            ],
            [ //guest mode. customers will be merged
                [$this->getCustomer('TEST@test.com', 1), $this->getCustomer('test@test.com', 1)],
                [$this->getUniqueHash('test@test.com', 1) => $this->getCustomer('test@test.com', 1)]
            ],
            [ //guest mode. customers will be merged
                [$this->getCustomer('example!user@e.com', 1), $this->getCustomer('example!user@e.com', 1)],
                [$this->getUniqueHash('example!user@e.com', 1) => $this->getCustomer('example!user@e.com', 1)]
            ],
            [ //guest mode. customers will be merged
                [$this->getCustomer('example&@e.com', 1), $this->getCustomer('example&@e.com', 1)],
                [$this->getUniqueHash('example&@e.com', 1) => $this->getCustomer('example&@e.com', 1)]
            ],
            [ //guest mode. customers won't be merged, different channel
                [$this->getCustomer('test@test.com', 1), $this->getCustomer('test@test.com', 2)],
                [
                    $this->getUniqueHash('test@test.com', 1) => $this->getCustomer('test@test.com', 1),
                    $this->getUniqueHash('test@test.com', 2) => $this->getCustomer('test@test.com', 2)
                ]
            ],
            [ //guest mode. customers won't be merged, different email
                [$this->getCustomer('tests@test.com', 1), $this->getCustomer('test@test.com', 1)],
                [
                    $this->getUniqueHash('tests@test.com', 1) => $this->getCustomer('tests@test.com', 1),
                    $this->getUniqueHash('test@test.com', 1) => $this->getCustomer('test@test.com', 1)
                ]
            ],
            [ //guest mode. customers will be merged, without channel
                [$this->getCustomer('example&@e.com', null), $this->getCustomer('example&@e.com', null)],
                [$this->getUniqueHash('example&@e.com', null) => $this->getCustomer('example&@e.com', null)]
            ],
            [ //not guest mode. customers will be merged with originId
                [$this->getCustomer('example@e.com', 1, false, 10), $this->getCustomer('example@e.com', 1, false, 10)],
                [10 => $this->getCustomer('example@e.com', 1, false, 10)]
            ]
        ];
    }

    /**
     * @param string $email
     * @param int $channelId
     *
     * @return string
     */
    public function getUniqueHash($email, $channelId = null)
    {
        $string = $email;

        if ($channelId) {
            $string.=$channelId;
        }

        return md5($string);
    }
}
