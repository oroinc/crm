<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Unit\ImportExport\Converter;

use OroCRM\Bundle\MagentoBundle\ImportExport\Converter\AttributesConverterHelper;

class AttributesConverterHelperTest extends \PHPUnit_Framework_TestCase
{
    public function testAddUnknownAttributesNoAttributes()
    {
        $context = $this->getMockBuilder('Oro\Bundle\ImportExportBundle\Context\ContextInterface')
            ->getMock();

        $data = [
            'test' => '1'
        ];

        $this->assertEquals($data, AttributesConverterHelper::addUnknownAttributes($data, $context));
    }

    public function testAddUnknownAttributesSimpleAttributes()
    {
        $context = $this->getMockBuilder('Oro\Bundle\ImportExportBundle\Context\ContextInterface')
            ->getMock();

        $data = [
            'test' => '1',
            'existing' => '2',
            'existingCamelCase' => 3,
            AttributesConverterHelper::ATTRIBUTES_KEY => [
                [
                    AttributesConverterHelper::KEY => 'some_attribute_one',
                    AttributesConverterHelper::VALUE => 'val1'
                ],
                [
                    AttributesConverterHelper::KEY => 'someAttributeTwo',
                    AttributesConverterHelper::VALUE => 'val2'
                ],
                [
                    AttributesConverterHelper::KEY => 'existing',
                    AttributesConverterHelper::VALUE => 'val3'
                ],
                [
                    AttributesConverterHelper::KEY => 'existing_camel_case',
                    AttributesConverterHelper::VALUE => 'val4'
                ],
            ]
        ];

        $expected = [
            'test' => '1',
            'existing' => '2',
            'existingCamelCase' => 3,
            'someAttributeOne' => 'val1',
            'someAttributeTwo' => 'val2'
        ];

        $this->assertEquals($expected, AttributesConverterHelper::addUnknownAttributes($data, $context));
    }

    public function testAddUnknownAttributesIdAttributesNoChannel()
    {
        $context = $this->getMockBuilder('Oro\Bundle\ImportExportBundle\Context\ContextInterface')
            ->getMock();

        $data = [
            'test' => '1',
            AttributesConverterHelper::ATTRIBUTES_KEY => [
                [
                    AttributesConverterHelper::KEY => 'some_id',
                    AttributesConverterHelper::VALUE => 'val1'
                ]
            ]
        ];

        $expected = [
            'test' => '1',
            'someId' => 'val1'
        ];

        $this->assertEquals($expected, AttributesConverterHelper::addUnknownAttributes($data, $context));
    }

    public function testAddUnknownAttributesIdAttributes()
    {
        $context = $this->getMockBuilder('Oro\Bundle\ImportExportBundle\Context\ContextInterface')
            ->getMock();
        $context->expects($this->once())
            ->method('hasOption')
            ->with(AttributesConverterHelper::CHANNEL_KEY)
            ->will($this->returnValue(true));
        $context->expects($this->once())
            ->method('getOption')
            ->with(AttributesConverterHelper::CHANNEL_KEY)
            ->will($this->returnValue(1));

        $data = [
            'test' => '1',
            AttributesConverterHelper::ATTRIBUTES_KEY => [
                [
                    AttributesConverterHelper::KEY => 'some_id',
                    AttributesConverterHelper::VALUE => 'val1'
                ]
            ]
        ];

        $expected = [
            'test' => '1',
            'someId' => 'val1',
            'some' => [
                'originId' => 'val1',
                'channel' => [
                    'id' => 1
                ]
            ]
        ];

        $this->assertEquals($expected, AttributesConverterHelper::addUnknownAttributes($data, $context));
    }
}
