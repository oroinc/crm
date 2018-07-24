<?php

namespace Oro\Bundle\FormBundle\Tests\Unit\Form\Type;

use Oro\Bundle\MagentoBundle\Form\DataTransformer\EmailListToStringTransformer;

class EmailListToStringTransformerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider transformDataProvider
     * @param string $delimiter
     * @param boolean $filterUinqueValues
     * @param mixed $value
     * @param mixed $expectedValue
     */
    public function testTransform($delimiter, $filterUinqueValues, $value, $expectedValue)
    {
        $transformer = $this->createTestTransfomer($delimiter, $filterUinqueValues);
        $this->assertEquals($expectedValue, $transformer->transform($value));
    }

    public function transformDataProvider()
    {
        return [
            'default' => [
                [',', ';'],
                false,
                [1, 2, 3, 4],
                '1,2,3,4',
            ],
            'null' => [
                [','],
                false,
                null,
                ''
            ],
            'empty array' => [
                [',', ';'],
                false,
                [],
                ''
            ],
            'filter unique values on' => [
                [';', ','],
                true,
                [1, 1, 2, 2, 3, 3, 4, 4],
                '1,2,3,4'
            ],
            'filter unique values off' => [
                [';', ','],
                false,
                [1, 1, 2, 2, 3, 3, 4, 4],
                '1,1,2,2,3,3,4,4'
            ]
        ];
    }

    /**
     * @expectedException \Symfony\Component\Form\Exception\UnexpectedTypeException
     * @expectedExceptionMessage Expected argument of type "array", "string" given
     */
    public function testTransformFailsWhenUnexpectedType()
    {
        $transformer = $this->createTestTransfomer();
        $transformer->transform('');
    }

    /**
     * @dataProvider reverseTransformDataProvider
     *
     * @param string $delimiter
     * @param boolean $filterUinqueValues
     * @param mixed $value
     * @param mixed $expectedValue
     */
    public function testReverseTransform($delimiter, $filterUinqueValues, $value, $expectedValue)
    {
        $transformer = $this->createTestTransfomer($delimiter, $filterUinqueValues);
        $this->assertEquals($expectedValue, $transformer->reverseTransform($value));
    }

    /**
     * @return array
     */
    public function reverseTransformDataProvider()
    {
        return [
            'default' => [
                [';', ','],
                false,
                '1;2;3;4',
                ['1', '2', '3', '4']
            ],
            'null' => [
                [','],
                false,
                null,
                []
            ],
            'empty string' => [
                [','],
                false,
                '',
                []
            ],
            'trim and empty values' => [
                ['|', ';', ','],
                false,
                ' | 1 | 2 | | 3 | 4|  ',
                ['1', '2', '3', '4']
            ],
            'filter unique values on' => [
                [',', '|', ';'],
                true,
                '1|1;2|2,3,3,4,4',
                ['1', '2', '3', '4']
            ],
            'filter unique values off' => [
                [','],
                false,
                '1,1,2,2,3,3,4,4',
                ['1', '1', '2', '2', '3', '3', '4', '4']
            ],
            'space delimiter' => [
                [' ', ','],
                false,
                ' 1  2  3  4 ',
                ['1', '2', '3', '4']
            ],
        ];
    }

    /**
     * @expectedException \Symfony\Component\Form\Exception\UnexpectedTypeException
     * @expectedExceptionMessage Expected argument of type "string", "array" given
     */
    public function testReverseTransformFailsWhenUnexpectedType()
    {
        $this->createTestTransfomer()->reverseTransform([]);
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Default delimiter ',', should be included in available delimiters list
     */
    public function testDelimiterIsNotAvailable()
    {
        return new EmailListToStringTransformer(['|', ';'], ',');
    }

    /**
     * @param array $delimiters
     * @param boolean $filterUniqueValues
     *
     * @return EmailListToStringTransformer
     */
    private function createTestTransfomer($delimiters = [',', ';'], $filterUniqueValues = false)
    {
        return new EmailListToStringTransformer($delimiters, ',', $filterUniqueValues);
    }
}
