<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Unit\Provider\Strategy;

use OroCRM\Bundle\MagentoBundle\Provider\Strategy\TwoWaySyncStrategy;

class TwoWaySyncStrategyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var TwoWaySyncStrategy
     */
    protected $strategy;

    protected function setUp()
    {
        $this->strategy = new TwoWaySyncStrategy();
    }

    protected function tearDown()
    {
        unset($this->strategy);
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
                        'old' => 'old value',
                        'new' => 'new value'
                    ]
                ],
                'localData' => ['prop' => 'value', 'prop2' => 'new value'],
                'remoteData' => ['prop' => 'new value', 'prop2' => 'old value'],
                'strategy' => 'remote',
                'expected' => ['prop' => 'new value', 'prop2' => 'old value']
            ],
            'local changes without conflict local wins' => [
                'changeSet' => [
                    'prop2' => [
                        'old' => 'old value',
                        'new' => 'new value'
                    ]
                ],
                'localData' => ['prop' => 'value', 'prop2' => 'new value'],
                'remoteData' => ['prop' => 'new value', 'prop2' => 'old value'],
                'strategy' => 'local',
                'expected' => ['prop' => 'new value', 'prop2' => 'old value']
            ],
            'local changes with conflict remote wins' => [
                'changeSet' => [
                    'prop2' => [
                        'old' => 'old value',
                        'new' => 'new value'
                    ]
                ],
                'localData' => ['prop' => 'value', 'prop2' => 'new value'],
                'remoteData' => ['prop' => 'new value', 'prop2' => 'new remote value'],
                'strategy' => 'remote',
                'expected' => ['prop' => 'new value', 'prop2' => 'new remote value']
            ],
            'local changes with conflict local wins' => [
                'changeSet' => [
                    'prop2' => [
                        'old' => 'old value',
                        'new' => 'new value'
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
                        'new' => 'new value'
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
                        'new' => 'new value'
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
                        'old' => 'old value',
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
                        'old' => 'old value',
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
                        'old' => 'old value',
                        'new' => null
                    ]
                ],
                'localData' => ['prop' => 'value', 'prop2' => null],
                'remoteData' => ['prop' => 'new value'],
                'strategy' => 'local',
                'expected' => ['prop' => 'new value']
            ],
        ];
    }
}
