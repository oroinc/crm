<?php

namespace Oro\Bundle\NavigationBundle\Tests\Unit\Title\TitleReader;

use Oro\Bundle\NavigationBundle\Title\TitleReader\ConfigReader;

class ConfigReaderTest extends \PHPUnit_Framework_TestCase
{
    const TEST_ROUTE = 'test_route';

    /**
     * @var ConfigReader
     */
    private $reader;

    public function setUp()
    {
        $this->reader = new ConfigReader(array(self::TEST_ROUTE => 'Test title template'));
    }

    /**
     * @dataProvider provider
     *
     * @param array $routes
     */
    public function testGetData(array $routes)
    {
        try {
            $data = $this->reader->getData($routes);

            $this->assertTrue(is_array($data));
            $this->assertEquals(1, count($data));
        } catch (\Exception $e) {
            $this->assertInstanceOf('Symfony\Component\Config\Definition\Exception\InvalidConfigurationException', $e);
        }
    }

    /**
     * Data provider
     *
     * @return array
     */
    public function provider()
    {
        return array(
            array(
                array(self::TEST_ROUTE => 'Test route data')
            ),

            array(
                array()
            )
        );
    }
}
