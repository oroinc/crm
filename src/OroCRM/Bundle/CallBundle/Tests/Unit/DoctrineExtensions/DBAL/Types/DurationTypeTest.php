<?php

namespace Oro\Bundle\CallBundle\Tests\Unit\DoctrineExtensions\DBAL\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Oro\Bundle\CallBundle\DoctrineExtensions\DBAL\Types\DurationType;

class DurationTypeTest extends \PHPUnit_Framework_TestCase
{
    /** @var DurationType */
    protected $type;

    /** @var AbstractPlatform */
    protected $platform;

    protected function setUp()
    {
        // class has private constructor
        $this->type = $this
            ->getMockBuilder(
                'Oro\Bundle\CallBundle\DoctrineExtensions\DBAL\Types\DurationType'
            )
            ->setMethods(null)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->platform = $this
            ->getMockBuilder('Doctrine\DBAL\Platforms\AbstractPlatform')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass()
        ;
    }

    /**
     * @param mixed $value
     * @param string $expected
     *
     * @dataProvider convertToDatabaseValueDataProvider
     */
    public function testConvertToDatabaseValue($value, $expected)
    {
        $this->assertSame(
            $expected,
            $this->type->convertToDatabaseValue($value, $this->platform)
        );
    }

    /**
     * @return array
     */
    public function convertToDatabaseValueDataProvider()
    {
        return [
            'null' => [null, null],
            'empty' => ['', ''],
            'string' => ['10', '10'],
            'integer' => [10, 10],
        ];
    }

    public function testConvertToPHPValue()
    {
        $this->assertEquals(10, $this->type->convertToPHPValue('10', $this->platform));
    }
}
