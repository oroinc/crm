<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\Converter\Rest;

use Oro\Bundle\MagentoBundle\Converter\Rest\WebsiteConverter;

/**
 * Class WebsiteConverterTest is a unit test class for WebsiteConverter
 */
class WebsiteConverterTest extends \PHPUnit\Framework\TestCase
{
    /** @var WebsiteConverter */
    private $converter;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->converter = new WebsiteConverter();
    }

    /**
     * Tests if conversion done properly
     *
     * @dataProvider getSourceWebsites
     */
    public function testConvert($sourceData, $expectedData)
    {
        $convertedWebsite = $this->converter->convert($sourceData);

        $this->assertEquals($expectedData, $convertedWebsite, 'Website REST data was not converted properly');
    }

    /**
     * Data provider for testConvert method
     *
     * @return array
     */
    public function getSourceWebsites()
    {
        return [
           'convert success with 2 websites' => [
               'source data' => [
                   [
                       'id' => 0,
                       'code' => 'admin',
                       'name' => 'Admin',
                   ],
                   [
                       'id' => 1,
                       'code' => 'custom',
                       'name' => 'Custom',
                   ]
               ],
               'expected data' => [
                   [
                       'website_id' => 0,
                       'code' => 'admin',
                       'name' => 'Admin',
                   ],
                   [
                       'website_id' => 1,
                       'code' => 'custom',
                       'name' => 'Custom',
                   ]
               ]
           ],
        ];
    }
}
