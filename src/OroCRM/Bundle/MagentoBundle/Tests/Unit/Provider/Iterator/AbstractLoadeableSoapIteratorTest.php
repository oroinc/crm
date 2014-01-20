<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Unit\Provider\Iterator;

use OroCRM\Bundle\MagentoBundle\Provider\Iterator\AbstractLoadeableSoapIterator;
use OroCRM\Bundle\MagentoBundle\Provider\Transport\SoapTransport;

class AbstractLoadeableSoapIteratorTest extends \PHPUnit_Framework_TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject|AbstractLoadeableSoapIterator */
    protected $iterator;

    /** @var \PHPUnit_Framework_MockObject_MockObject|SoapTransport */
    protected $transport;

    public function setUp()
    {
        $this->transport = $this->getMockBuilder('OroCRM\\Bundle\\MagentoBundle\\Provider\\Transport\\SoapTransport')
            ->disableOriginalConstructor()->getMock();

        $this->iterator = $this
            ->getMockBuilder('OroCRM\\Bundle\\MagentoBundle\\Provider\\Iterator\\AbstractLoadeableSoapIterator')
            ->setMethods(['getData'])
            ->setConstructorArgs([$this->transport])
            ->getMockForAbstractClass();

        $this->assertAttributeEquals($this->transport, 'transport', $this->iterator);
    }

    public function tearDown()
    {
        unset($this->iterator, $this->transport);
    }

    /**
     * @dataProvider iterationProvider
     *
     * @param array $data
     */
    public function testIteration(array $data)
    {
        // should called once even multiple iteration
        $this->iterator->expects($this->once())->method('getData')
            ->will($this->returnValue($data));

        $expectedKeys   = array_keys($data);
        $expectedValues = array_values($data);
        foreach (range(1, 2) as $numberOfIteration) {
            $keys = $values = [];

            foreach ($this->iterator as $key => $value) {
                $keys[]   = $key;
                $values[] = $value;
            }

            $this->assertSame($expectedKeys, $keys, 'Should return correct keys');
            $this->assertSame($expectedValues, $values, 'Should return correct values');
        }

        $this->assertSame($data, iterator_to_array($this->iterator), 'Should return correct data and do not call load');
        $this->assertCount(count($data), $this->iterator);
    }

    /**
     * @return array
     */
    public function iterationProvider()
    {
        return [
            'plain array' => [['test1', 'test2', 'test3']],
            'assoc array' => [['test1' => ['test'], 'test2' => 22]]
        ];
    }
}
