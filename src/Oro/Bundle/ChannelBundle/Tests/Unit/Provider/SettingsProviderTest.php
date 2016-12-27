<?php

namespace Oro\Bundle\ChannelBundle\Tests\Unit\Provider;

use Oro\Bundle\ChannelBundle\Provider\SettingsProvider;

class SettingsProviderTest extends \PHPUnit_Framework_TestCase
{
    const TEST_ENTITY_NAME         = 'TestBundle\Entity\Test';
    const TEST_ANOTHER_ENTITY_NAME = 'TestBundle\Entity\Test2';

    /**
     * @dataProvider channelEntityConfigProvider
     *
     * @param string $entityName
     * @param array  $config
     * @param bool   $expectedResult
     */
    public function testIsChannelEntity($entityName, $config, $expectedResult)
    {
        $this->assertSame($expectedResult, $this->getSettingsProvider($config)->isChannelEntity($entityName));
    }

    /**
     * @return array
     */
    public function channelEntityConfigProvider()
    {
        return [
            'empty config, entity is not channel related'                         => [
                self::TEST_ENTITY_NAME,
                ['entity_data' => []],
                false
            ],
            'config given, entity is not in config, so it\'s not channel related' => [
                self::TEST_ENTITY_NAME,
                [
                    'entity_data' => [
                        ['name' => 'SomeEntityName', 'dependent' => [], 'dependencies' => []]
                    ]
                ],
                false
            ],
            'config given, entity is  channel related'                            => [
                self::TEST_ENTITY_NAME,
                [
                    'entity_data' => [
                        ['name' => 'SomeEntityName', 'dependent' => [], 'dependencies' => []],
                        ['name' => self::TEST_ENTITY_NAME, 'dependent' => [], 'dependencies' => []]
                    ]
                ],
                true
            ]
        ];
    }

    /**
     * @dataProvider dependentEntityConfigProvider
     *
     * @param string $entityName
     * @param array  $config
     * @param bool   $expectedResult
     */
    public function testIsDependentOnChannelEntity($entityName, $config, $expectedResult)
    {
        $this->assertSame(
            $expectedResult,
            $this->getSettingsProvider($config)->isDependentOnChannelEntity($entityName)
        );
    }

    /**
     * @return array
     */
    public function dependentEntityConfigProvider()
    {
        return [
            'empty config, entity is not channel related'                           => [
                self::TEST_ENTITY_NAME,
                ['entity_data' => []],
                false
            ],
            'config given, entity is not in config, so it\'s not channel dependent' => [
                self::TEST_ENTITY_NAME,
                [
                    'entity_data' => [
                        ['name' => 'SomeEntityName', 'dependent' => [], 'dependencies' => []]
                    ]
                ],
                false
            ],
            'config given, entity is  channel related'                              => [
                self::TEST_ENTITY_NAME,
                [
                    'entity_data' => [
                        ['name' => 'SomeEntityName', 'dependent' => [self::TEST_ENTITY_NAME], 'dependencies' => []],
                    ]
                ],
                true
            ]
        ];
    }

    /**
     * @dataProvider configProvider
     *
     * @param array $config
     * @param bool  $expectedResult
     * @param null  $section
     */
    public function testGetSettings($config, $expectedResult, $section = null)
    {
        $provider = $this->getSettingsProvider($config);
        $this->assertSame($expectedResult, $provider->getSettings($section));
    }

    /**
     * @return array
     */
    public function configProvider()
    {
        return [
            'should return all config'                => [
                [
                    'entity_data'  => [
                        [
                            'name'         => self::TEST_ENTITY_NAME,
                            'dependent'    => [],
                            'dependencies' => []
                        ]
                    ],
                    'some_section' => 'test'
                ],
                [
                    'entity_data'  => [
                        self::TEST_ENTITY_NAME => [
                            'name'         => self::TEST_ENTITY_NAME,
                            'dependent'    => [],
                            'dependencies' => []
                        ]
                    ],
                    'some_section' => 'test'
                ]
            ],
            'should return asked section'             => [
                [
                    'entity_data'  => [],
                    'some_section' => 'test'
                ],
                [],
                'entity_data'
            ],
            'should return null if section not found' => [
                ['entity_data' => [],],
                null,
                'some_section'
            ]
        ];
    }

    /**
     * @dataProvider sourceTypesDataProvider
     *
     * @param array $config
     * @param array $expectedResults
     */
    public function testGetSourceIntegrationTypes(array $config, array $expectedResults)
    {
        $this->assertSame($expectedResults, $this->getSettingsProvider($config)->getSourceIntegrationTypes());
    }

    /**
     * @return array
     */
    public function sourceTypesDataProvider()
    {
        return [
            'no one integration comes with entities'                   => [
                '$config'          => [
                    'entity_data' => [
                        [
                            'name'         => self::TEST_ENTITY_NAME,
                            'dependent'    => [],
                            'dependencies' => []
                        ]
                    ],
                ],
                '$expectedResults' => []
            ],
            'should found one integration, should return unique array' => [
                '$config'          => [
                    'entity_data' => [
                        [
                            'name'         => self::TEST_ENTITY_NAME,
                            'dependent'    => [],
                            'dependencies' => [],
                            'belongs_to'   => ['integration' => 'test']
                        ],
                        [
                            'name'         => self::TEST_ANOTHER_ENTITY_NAME,
                            'dependent'    => [],
                            'dependencies' => [],
                            'belongs_to'   => ['integration' => 'test']
                        ]
                    ],
                    'channel_types' => [
                        'magento' => [
                            'label' => 'Magento type',
                            'entities' => [
                                'Oro\Bundle\MagentoBundle\Entity\Cart',
                                'Oro\Bundle\MagentoBundle\Entity\Customer',
                                'Oro\Bundle\MagentoBundle\Entity\Order'
                            ],
                            'integration_type' => 'magento',
                            'customer_identity' => 'Oro\Bundle\MagentoBundle\Entity\Customer',
                        ],
                        'custom' => [
                            'label' => 'Custom',
                            'entities' => [],
                        ]
                    ],
                ],
                '$expectedResults' => ['magento']
            ]
        ];
    }

    /**
     * @param array $settings
     *
     * @return SettingsProvider
     */
    protected function getSettingsProvider(array $settings)
    {
        $resolverMock = $this->createMock('Oro\Component\Config\Resolver\ResolverInterface');
        $resolverMock->expects($this->once())->method('resolve')
            ->with($this->equalTo($settings))
            ->will($this->returnArgument(0));

        return new SettingsProvider($settings, $resolverMock);
    }

    /**
     * @return array
     */
    public function channelTypesProvider()
    {
        return [
            'without channels types' => [
                '$config' => [
                    'entity_data' => [],
                    'channel_types' => [],
                ],
                '$expectedResults' => []
            ],
            'two channel type' => [
                '$config' => [
                    'entity_data' => [],
                    'channel_types' => [
                        'magento' => [
                            'label' => 'Magento type',
                            'entities' => [
                                'Oro\Bundle\MagentoBundle\Entity\Cart',
                                'Oro\Bundle\MagentoBundle\Entity\Customer',
                                'Oro\Bundle\MagentoBundle\Entity\Order'
                            ],
                            'integration_type' => 'magento',
                            'customer_identity' => 'Oro\Bundle\MagentoBundle\Entity\Customer',
                            'priority' => 0
                        ],
                        'custom' => [
                            'label' => 'Custom',
                            'entities' => [],
                            'priority' => -10
                        ]
                    ],
                ],
                '$expectedResults' => ['custom' => 'Custom', 'magento' => 'Magento type']
            ]
        ];
    }

    /**
     * @dataProvider channelTypesProvider
     *
     * @param array $config
     * @param array $expectedResults
     */
    public function testGetChannelTypeChoiceList(array $config, array $expectedResults)
    {
        $this->assertSame($expectedResults, $this->getSettingsProvider($config)->getChannelTypeChoiceList());
    }

    /**
     * @return array
     */
    public function channelConfigProvider()
    {
        return [
            'system channel' => [
                '$config' => [
                    'entity_data' => [],
                    'channel_types' => [
                        'custom' => [
                            'label' => 'Custom',
                            'system' => true
                        ]
                    ],
                ],
                '$expectedResults' => true
            ],
            'non system channel' => [
                '$config' => [
                    'entity_data' => [],
                    'channel_types' => [
                        'custom' => [
                            'label' => 'Custom',
                            'system' => false
                        ]
                    ],
                ],
                '$expectedResults' => false
            ],
            'default channel type' => [
                '$config' => [
                    'entity_data' => [],
                    'channel_types' => [
                        'custom' => [
                            'label' => 'Custom'
                        ]
                    ],
                ],
                '$expectedResults' => false
            ]
        ];
    }

    /**
     * @dataProvider channelConfigProvider
     *
     * @param array $config
     * @param $expectedResults
     */
    public function testIsSystemChannel(array $config, $expectedResults)
    {
        $this->assertEquals($expectedResults, $this->getSettingsProvider($config)->isChannelSystem('custom'));
    }
}
