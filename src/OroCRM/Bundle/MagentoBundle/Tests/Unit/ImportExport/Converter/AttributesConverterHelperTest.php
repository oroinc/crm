<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Unit\ImportExport\Converter;

use Oro\Bundle\ImportExportBundle\Context\Context;

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
        $context = new Context([
            AttributesConverterHelper::ENTITY_NAME_KEY => 'Oro\Bundle\UserBundle\Entity\User',
        ]);

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
                [
                    AttributesConverterHelper::KEY => 'emAil',
                    AttributesConverterHelper::VALUE => 'em@example.com'
                ],
            ]
        ];

        $expected = [
            'test' => '1',
            'existing' => '2',
            'existingCamelCase' => 3,
            'someAttributeOne' => 'val1',
            'someAttributeTwo' => 'val2',
            'email' => 'em@example.com',
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
        $context = new Context([
            AttributesConverterHelper::CHANNEL_KEY => true,
            AttributesConverterHelper::CHANNEL_KEY => 1,
        ]);

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

    /**
     * @dataProvider normalizePropertiesProvider
     */
    public function testNormalizeProperties($record, $properties, $contextConfiguration, $expectedRecord)
    {
        $context = new Context($contextConfiguration);

        $this->assertEquals(
            $expectedRecord,
            AttributesConverterHelper::normalizeProperties($record, $properties, $context)
        );
    }

    public function normalizePropertiesProvider()
    {
        return [
            [
                'record' => [
                    'username' => 'user',
                    'email' => 'email@example.com',
                ],
                'properties' => [
                    'email',
                ],
                'contextConfiguration' => [
                    AttributesConverterHelper::ENTITY_NAME_KEY => 'Oro\Bundle\UserBundle\Entity\User',
                ],
                'expectedRecord' => [
                    'username' => 'user',
                    'email' => 'email@example.com',
                ],
            ],
            [
                'record' => [
                    'username' => 'user',
                    'eMail' => 'email@example.com',
                ],
                'properties' => [
                    'eMail',
                ],
                'contextConfiguration' => [
                    AttributesConverterHelper::ENTITY_NAME_KEY => 'Oro\Bundle\UserBundle\Entity\User',
                ],
                'expectedRecord' => [
                    'username' => 'user',
                    'email' => 'email@example.com',
                ],
            ],
            [
                'record' => [
                    'username' => 'user',
                    'eMail' => 'email@example.com',
                ],
                'properties' => [
                    'username',
                ],
                'contextConfiguration' => [
                    AttributesConverterHelper::ENTITY_NAME_KEY => 'Oro\Bundle\UserBundle\Entity\User',
                ],
                'expectedRecord' => [
                    'username' => 'user',
                    'eMail' => 'email@example.com',
                ],
            ],
        ];
    }
}
