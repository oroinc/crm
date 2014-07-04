<?php

namespace OroCRM\Bundle\ChannelBundle\Tests\Unit\Provider;

use OroCRM\Bundle\ChannelBundle\Provider\SettingsProvider;

class SettingsProviderTest extends \PHPUnit_Framework_TestCase
{
    const TEST_ENTITY_NAME = 'TestBundle\Entity\Test';

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
     * @dataProvider belongsToIntegrationConfigProvider
     *
     * @param string $entityName
     * @param array  $config
     * @param bool   $expectedResult
     */
    public function testBelongsToIntegration($entityName, $config, $expectedResult)
    {
        $this->assertSame(
            $expectedResult,
            $this->getSettingsProvider($config)->belongsToIntegration($entityName)
        );
    }

    /**
     * @return array
     */
    public function belongsToIntegrationConfigProvider()
    {
        return [
            'empty config, entity is not channel related'                            => [
                self::TEST_ENTITY_NAME,
                ['entity_data' => []],
                false
            ],
            'config given, entity is in config, but does not belongs to integration' => [
                self::TEST_ENTITY_NAME,
                [
                    'entity_data' => [
                        ['name' => self::TEST_ENTITY_NAME, 'dependent' => [], 'dependencies' => []]
                    ]
                ],
                false
            ],
            'config given, entity belongs to integration'                            => [
                self::TEST_ENTITY_NAME,
                [
                    'entity_data' => [
                        [
                            'name'                   => self::TEST_ENTITY_NAME,
                            'dependent'              => [],
                            'dependencies'           => [],
                            'belongs_to_integration' => 'test'
                        ],
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
     * @param array $settings
     *
     * @return SettingsProvider
     */
    protected function getSettingsProvider(array $settings)
    {
        $resolverMock = $this->getMock('Oro\Component\Config\Resolver\ResolverInterface');
        $resolverMock->expects($this->once())->method('resolve')
            ->with($this->equalTo($settings))
            ->will($this->returnArgument(0));

        return new SettingsProvider($settings, $resolverMock);
    }
}
