<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\Provider\Iterator;

use Oro\Bundle\MagentoBundle\Provider\Iterator\Soap\AbstractLoadeableSoapIterator;
use Oro\Bundle\MagentoBundle\Tests\Unit\Provider\Iterator\Stub\AbstractLoadableSoapIteratorStub;
use PHPUnit\Framework\MockObject\MockObject;

class AbstractLoadeableSoapIteratorTest extends BaseSoapIteratorTestCase
{
    /** @var MockObject|AbstractLoadeableSoapIterator */
    protected $iterator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->iterator = $this
            ->getMockBuilder(AbstractLoadableSoapIteratorStub::class)
            ->onlyMethods(['getData'])
            ->setConstructorArgs([$this->transport])
            ->getMockForAbstractClass();
    }

    public function testConstructorSetsTransport()
    {
        static::assertSame($this->transport, $this->iterator->getTransport());
    }

    /**
     * @dataProvider iterationProvider
     *
     * @param array $data
     */
    public function testIteration(array $data)
    {
        // should called once even multiple iteration
        $this->iterator->expects(static::once())
            ->method('getData')
            ->willReturn($data);

        $expectedKeys   = array_keys($data);
        $expectedValues = array_values($data);

        // iteration 1
        $keys = $values = [];
        foreach ($this->iterator as $key => $value) {
            $keys[]   = $key;
            $values[] = $value;
        }
        static::assertSame($expectedKeys, $keys, 'Should return correct keys');
        static::assertSame($expectedValues, $values, 'Should return correct values');

        // iteration 2
        $keys = $values = [];
        foreach ($this->iterator as $key => $value) {
            $keys[]   = $key;
            $values[] = $value;
        }
        static::assertSame($expectedKeys, $keys, 'Should return correct keys');
        static::assertSame($expectedValues, $values, 'Should return correct values');

        static::assertSame(
            $data,
            \iterator_to_array($this->iterator),
            'Should return correct data and do not call load'
        );
        static::assertCount(count($data), $this->iterator);
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
