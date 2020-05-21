<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\Provider\Transport;

use Oro\Bundle\IntegrationBundle\Provider\Rest\Transport\RestTransportSettingsInterface;
use Oro\Bundle\MagentoBundle\Entity\MagentoRestTransport;
use Oro\Bundle\MagentoBundle\Provider\Transport\RestTransportAdapter;

class RestTransportAdapterTest extends \PHPUnit\Framework\TestCase
{
    /** @var  RestTransportAdapter */
    protected $instance;

    /** @var  MagentoRestTransport */
    protected $transportEntity;

    protected function setUp(): void
    {
        $this->transportEntity = new MagentoRestTransport();
    }

    public function testSuccess()
    {
        $instance = $this->getInstance([]);
        $this->assertInstanceOf(RestTransportSettingsInterface::class, $instance);
    }

    public function testGetBaseUrl()
    {
        $this->transportEntity->setApiUrl('MagentoBaseURL');
        $instance = $this->getInstance([]);
        $this->assertEquals('MagentoBaseURL/rest/V1', $instance->getBaseUrl());
    }

    public function testGetDefaultOptions()
    {
        $expectedOptions = [
            'headers' => [
                'Accept' => 'application/json'
            ]
        ];

        $instance = $this->getInstance([]);
        $this->assertEquals($expectedOptions, $instance->getOptions(), "Array of default headers expected");
    }

    public function testAddExtraOption()
    {
        $expectedOptions = [
            'headers' => [
                'Accept' => 'application/json'
            ],
            'foo' => 'bar'
        ];

        $instance = $this->getInstance([
            'foo' => 'bar'
        ]);

        $this->assertEquals($expectedOptions, $instance->getOptions(), "Additional options not merged");
    }

    public function testOverrideOption()
    {
        $expectedOptions = [
            'headers' => [
                'Accept' => 'newValue'
            ]
        ];

        $instance = $this->getInstance($expectedOptions);

        $this->assertEquals($expectedOptions, $instance->getOptions(), "Default options not overrided");
    }

    /**
     * @param $expectedOptions
     *
     * @return RestTransportAdapter
     */
    protected function getInstance($expectedOptions)
    {
        return new RestTransportAdapter(
            $this->transportEntity,
            $expectedOptions
        );
    }
}
