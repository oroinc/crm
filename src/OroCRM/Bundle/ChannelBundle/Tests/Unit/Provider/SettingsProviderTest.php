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
