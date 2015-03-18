<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Unit\Provider\Strategy;

use Oro\Bundle\ImportExportBundle\Converter\DataConverterInterface;
use OroCRM\Bundle\MagentoBundle\Provider\Strategy\TwoWaySyncStrategy;

class TwoWaySyncStrategyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var TwoWaySyncStrategy
     */
    protected $strategy;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|DataConverterInterface
     */
    protected $dataConverter;

    protected function setUp()
    {
        $this->dataConverter = $this->getMock('Oro\Bundle\ImportExportBundle\Converter\DataConverterInterface');

        $this->dataConverter->expects($this->any())
            ->method('convertToExportFormat')
            ->will(
                $this->returnCallback(
                    function ($item) {
                        $keys = array_map(
                            function ($key) {
                                return preg_replace(
                                    '/(^|[a-z])([A-Z])/e',
                                    'strtolower(strlen("\\1") ? "\\1_\\2" : "\\2")',
                                    $key
                                );
                            },
                            array_keys($item)
                        );

                        return array_combine($keys, array_values($item));
                    }
                )
            );


        $this->strategy = new TwoWaySyncStrategy($this->dataConverter);
    }

    protected function tearDown()
    {
        unset($this->strategy, $this->dataConverter);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Strategy "wrong" is not supported, expected one of "remote,local"
     */
    public function testWrongStrategy()
    {
        $this->strategy->merge([], [], [], 'wrong');
    }

    /**
     * @param array $changeSet
     * @param array $localData
     * @param array $remoteData
     * @param string $strategy
     * @param array $expected
     *
     * @dataProvider mergeDataProvider
     */
    public function testMergeRemoteWins(
        array $changeSet,
        array $localData,
        array $remoteData,
        $strategy,
        array $expected
    ) {
        $this->assertEquals(
            $expected,
            $this->strategy->merge($changeSet, $localData, $remoteData, $strategy)
        );
    }

    /**
     * @return array
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function mergeDataProvider()
    {
        return [
            'no local changes remote wins' => [
                'changeSet' => [],
                'localData' => ['prop' => 'value'],
                'remoteData' => ['prop' => 'new value'],
                'strategy' => 'remote',
                'expected' => ['prop' => 'new value']
            ],
            'no local changes local wins' => [
                'changeSet' => [],
                'localData' => ['prop' => 'value'],
                'remoteData' => ['prop' => 'new value'],
                'strategy' => 'local',
                'expected' => ['prop' => 'new value']
            ],
            'local changes without conflict remote wins' => [
                'changeSet' => [
                    'prop2' => [
                        'old' => 'old local value',
                        'new' => 'new local value'
                    ]
                ],
                'localData' => ['prop' => 'value', 'prop2' => 'new local value'],
                'remoteData' => ['prop' => 'new value', 'prop2' => 'old remote value'],
                'strategy' => 'remote',
                'expected' => ['prop' => 'new value', 'prop2' => 'old remote value']
            ],
            'local changes without conflict local wins' => [
                'changeSet' => [
                    'prop2' => [
                        'old' => 'old local value',
                        'new' => 'new local value'
                    ]
                ],
                'localData' => ['prop' => 'value', 'prop2' => 'new local value'],
                'remoteData' => ['prop' => 'new value', 'prop2' => 'old remote value'],
                'strategy' => 'local',
                'expected' => ['prop' => 'new value', 'prop2' => 'new local value']
            ],
            'local changes with conflict remote wins' => [
                'changeSet' => [
                    'prop2' => [
                        'old' => 'old local value',
                        'new' => 'new local value'
                    ]
                ],
                'localData' => ['prop' => 'value', 'prop2' => 'new local value'],
                'remoteData' => ['prop' => 'new value', 'prop2' => 'new remote value'],
                'strategy' => 'remote',
                'expected' => ['prop' => 'new value', 'prop2' => 'new remote value']
            ],
            'local changes with conflict local wins' => [
                'changeSet' => [
                    'prop2' => [
                        'old' => 'old local value',
                        'new' => 'new local value'
                    ]
                ],
                'localData' => ['prop' => 'value', 'prop2' => 'new local value'],
                'remoteData' => ['prop' => 'new value', 'prop2' => 'new remote value'],
                'strategy' => 'local',
                'expected' => ['prop' => 'new value', 'prop2' => 'new local value']
            ],
            'local changes with conflict local wins empty old value' => [
                'changeSet' => [
                    'prop2' => [
                        'old' => null,
                        'new' => 'new local value'
                    ]
                ],
                'localData' => ['prop' => 'value', 'prop2' => 'new local value'],
                'remoteData' => ['prop' => 'new value', 'prop2' => 'new remote value'],
                'strategy' => 'local',
                'expected' => ['prop' => 'new value', 'prop2' => 'new local value']
            ],
            'local changes with conflict remote wins empty old value' => [
                'changeSet' => [
                    'prop2' => [
                        'old' => null,
                        'new' => 'new local value'
                    ]
                ],
                'localData' => ['prop' => 'value', 'prop2' => 'new local value'],
                'remoteData' => ['prop' => 'new value', 'prop2' => 'new remote value'],
                'strategy' => 'remote',
                'expected' => ['prop' => 'new value', 'prop2' => 'new remote value']
            ],
            'local changes with conflict remote wins empty new value' => [
                'changeSet' => [
                    'prop2' => [
                        'old' => 'old local value',
                        'new' => null
                    ]
                ],
                'localData' => ['prop' => 'value', 'prop2' => 'new local value'],
                'remoteData' => ['prop' => 'new value', 'prop2' => 'new remote value'],
                'strategy' => 'remote',
                'expected' => ['prop' => 'new value', 'prop2' => 'new remote value']
            ],
            'local changes with conflict local wins empty new value' => [
                'changeSet' => [
                    'prop2' => [
                        'old' => 'old local value',
                        'new' => null
                    ]
                ],
                'localData' => ['prop' => 'value', 'prop2' => null],
                'remoteData' => ['prop' => 'new value', 'prop2' => 'new remote value'],
                'strategy' => 'local',
                'expected' => ['prop' => 'new value', 'prop2' => null]
            ],
            'multiple changes remote wins' => [
                'changeSet' => [
                    'prop1' => [
                        'old' => 'old value 1',
                        'new' => null
                    ],
                    'prop2' => [
                        'old' => 'old value 2',
                        'new' => 'new value 2'
                    ],
                    'prop3' => [
                        'old' => null,
                        'new' => 'new value 3'
                    ]
                ],
                'localData' => [
                    'prop0' => 'value',
                    'prop1' => 'value',
                    'prop2' => 'new local value',
                    'prop3' => 'new local value3'
                ],
                'remoteData' => [
                    'prop0' => 'remote value',
                    'prop1' => 'new value',
                    'prop2' => 'new remote value',
                    'prop3' => 'new remote value3'
                ],
                'strategy' => 'remote',
                'expected' => [
                    'prop0' => 'remote value',
                    'prop1' => 'new value',
                    'prop2' => 'new remote value',
                    'prop3' => 'new remote value3'
                ]
            ],
            'multiple changes local wins' => [
                'changeSet' => [
                    'prop1' => [
                        'old' => 'old value 1',
                        'new' => null
                    ],
                    'prop2' => [
                        'old' => 'old value 2',
                        'new' => 'new value 2'
                    ],
                    'prop3' => [
                        'old' => null,
                        'new' => 'new value 3'
                    ]
                ],
                'localData' => [
                    'prop0' => 'value',
                    'prop1' => 'value',
                    'prop2' => 'new local value',
                    'prop3' => 'new local value3'
                ],
                'remoteData' => [
                    'prop0' => 'remote value',
                    'prop1' => 'new value',
                    'prop2' => 'new remote value',
                    'prop3' => 'new remote value3'
                ],
                'strategy' => 'local',
                'expected' => [
                    'prop0' => 'remote value',
                    'prop1' => 'value',
                    'prop2' => 'new local value',
                    'prop3' => 'new local value3'
                ]
            ],
            'remote data does not contains changed field' => [
                'changeSet' => [
                    'prop2' => [
                        'old' => 'old local value',
                        'new' => null
                    ]
                ],
                'localData' => ['prop' => 'value', 'prop2' => null],
                'remoteData' => ['prop' => 'new remote value'],
                'strategy' => 'local',
                'expected' => ['prop' => 'new remote value', 'prop2' => null]
            ],
            'data converter' => [
                'changeSet' => ['propValue' => ['old' => 'old local value', 'new' => 'new local value']],
                'localData' => ['prop_value' => 'value'],
                'remoteData' => ['prop_value' => 'new remote value'],
                'strategy' => 'remote',
                'expected' => ['prop_value' => 'new remote value']
            ],
            'update not conflicted remote data' => [
                'changeSet' => [
                    'propValue' => ['old' => 'old local value', 'new' => 'new local value'],
                    'propValue2' => ['old' => 'old local value to remote', 'new' => 'new local value to remote']
                ],
                'localData' => ['prop_value' => 'value', 'prop_value2' => 'new local value to remote'],
                'remoteData' => ['prop_value' => 'new remote value', 'prop_value2' => 'old local value to remote'],
                'strategy' => 'remote',
                'expected' => ['prop_value' => 'new remote value', 'prop_value2' => 'new local value to remote']
            ]
        ];
    }
}
