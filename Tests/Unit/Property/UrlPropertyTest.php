<?php

namespace Oro\Bundle\GridBundle\Tests\Unit\Property;

use Oro\Bundle\GridBundle\Property\UrlProperty;
use Symfony\Bundle\FrameworkBundle\Routing\Router;

class UrlPropertyTest extends \PHPUnit_Framework_TestCase
{
    const TEST_PROPERTY_NAME = 'property_name';
    const TEST_ROUTE_NAME = 'route_name';

    /**
     * @var Router|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $router;

    protected function setUp()
    {
        $this->router = $this->getMockBuilder('Symfony\Bundle\FrameworkBundle\Routing\Router')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @param array $placeholders
     * @param bool $isAbsolute
     * @return UrlProperty
     */
    protected function createUrlProperty(
        array $placeholders = array(),
        $isAbsolute = false
    ) {
        return new UrlProperty(
            self::TEST_PROPERTY_NAME,
            $this->router,
            self::TEST_ROUTE_NAME,
            $placeholders,
            $isAbsolute
        );
    }

    public function testGetName()
    {
        $property = $this->createUrlProperty();
        $this->assertEquals(self::TEST_PROPERTY_NAME, $property->getName());
    }

    /**
     * @dataProvider getValueDataProvider
     */
    public function testGetValue(
        $expectedParameters,
        $data,
        $placeholders = array(),
        $isAbsolute = false
    ) {
        $expectedResult = 'test';
        $property = $this->createUrlProperty($placeholders, $isAbsolute);

        $this->router->expects($this->once())
            ->method('generate')
            ->with(self::TEST_ROUTE_NAME, $expectedParameters, $isAbsolute)
            ->will($this->returnValue($expectedResult));

        $this->assertEquals($expectedResult, $property->getValue($data));
    }

    /**
     * @return array
     */
    public function getValueDataProvider()
    {
        return array(
            'no route parameters' => array(
                'expectedParameters' => array(),
                'data' => array()
            ),
            'has placeholders' => array(
                'expectedParameters' => array(
                    'id' => 1
                ),
                'data' => array(
                    'id' => 1,
                    'name' => 'Test name',
                ),
                'placeholders' => array(
                    'id'
                ),
                'isAbsolute' => true
            ),
            'has placeholders as associated array' => array(
                'expectedParameters' => array(
                    'id' => 1
                ),
                'data' => array(
                    '_id_' => 1,
                    'name' => 'Test name',
                ),
                'placeholders' => array(
                    'id' => '_id_'
                )
            )
        );
    }
}
