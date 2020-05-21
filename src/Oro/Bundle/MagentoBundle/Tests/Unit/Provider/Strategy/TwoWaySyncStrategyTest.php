<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\Provider\Strategy;

use Doctrine\Common\Util\Inflector;
use Oro\Bundle\IntegrationBundle\ImportExport\Processor\StepExecutionAwareExportProcessor;
use Oro\Bundle\IntegrationBundle\ImportExport\Processor\StepExecutionAwareImportProcessor;
use Oro\Bundle\MagentoBundle\Provider\Strategy\TwoWaySyncStrategy;

class TwoWaySyncStrategyTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var TwoWaySyncStrategy
     */
    protected $strategy;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|StepExecutionAwareImportProcessor
     */
    protected $importProcessor;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|StepExecutionAwareExportProcessor
     */
    protected $exportProcessor;

    protected function setUp(): void
    {
        $this->importProcessor = $this
            ->getMockBuilder('Oro\Bundle\IntegrationBundle\ImportExport\Processor\StepExecutionAwareImportProcessor')
            ->disableOriginalConstructor()
            ->getMock();

        $this->exportProcessor = $this
            ->getMockBuilder('Oro\Bundle\IntegrationBundle\ImportExport\Processor\StepExecutionAwareExportProcessor')
            ->disableOriginalConstructor()
            ->getMock();

        $this->importProcessor->expects($this->any())
            ->method('process')
            ->will(
                $this->returnCallback(
                    function ($item) {
                        $keys = array_map(
                            function ($key) {
                                return Inflector::camelize($key);
                            },
                            array_keys($item)
                        );

                        return (object)array_combine($keys, array_values($item));
                    }
                )
            );

        $this->exportProcessor->expects($this->any())
            ->method('process')
            ->will(
                $this->returnCallback(
                    function ($item) {
                        $item = (array)$item;

                        $keys = array_map(
                            function ($key) {
                                return Inflector::tableize($key);
                            },
                            array_keys($item)
                        );

                        return array_combine($keys, array_values($item));
                    }
                )
            );

        $doctrineHelper = $this->getMockBuilder('Oro\Bundle\EntityBundle\ORM\DoctrineHelper')
            ->disableOriginalConstructor()
            ->getMock();

        $this->strategy = new TwoWaySyncStrategy(
            $this->importProcessor,
            $this->exportProcessor,
            $doctrineHelper
        );
    }

    protected function tearDown(): void
    {
        unset($this->strategy, $this->importProcessor, $this->exportProcessor);
    }

    public function testWrongStrategy()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Strategy "wrong" is not supported, expected one of "remote,local"');

        $this->strategy->merge([], [], [], 'wrong');
    }

    /**
     * @param array $changeSet
     * @param array $localData
     * @param array $remoteData
     * @param string $strategy
     * @param array $expected
     * @param array $additionalFields
     *
     * @dataProvider mergeDataProvider
     */
    public function testMerge(
        array $changeSet,
        array $localData,
        array $remoteData,
        $strategy,
        array $expected,
        array $additionalFields = []
    ) {
        $this->assertEquals(
            $expected,
            $this->strategy->merge($changeSet, $localData, $remoteData, $strategy, $additionalFields)
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
            'no local changes normalization' => [
                'changeSet' => [],
                'localData' => ['user_name' => 'john'],
                'remoteData' => ['userName' => 'john'],
                'strategy' => 'local',
                'expected' => ['user_name' => 'john']
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
            'normalization' => [
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
            ],
            'additional fields remote wins' => [
                'changeSet' => [
                    'propValue' => ['old' => 'old local value', 'new' => 'new local value'],
                    'propValue2' => ['old' => 'old local value to remote', 'new' => 'new local value to remote']
                ],
                'localData' => [
                    'prop_value' => 'value',
                    'prop_value2' => 'new local value to remote',
                    'prop3' => 'prop3Value local'
                ],
                'remoteData' => [
                    'prop_value' => 'new remote value',
                    'prop_value2' => 'old local value to remote',
                    'prop3' => 'prop3Value'
                ],
                'strategy' => 'remote',
                'expected' => [
                    'prop_value' => 'new remote value',
                    'prop_value2' => 'new local value to remote',
                    'prop3' => 'prop3Value'
                ],
                'additionalFields' => ['prop3']
            ],
            'additional fields local wins' => [
                'changeSet' => [
                    'propValue' => ['old' => 'old local value', 'new' => 'new local value'],
                    'propValue2' => ['old' => 'old local value to remote', 'new' => 'new local value to remote']
                ],
                'localData' => [
                    'prop_value' => 'value',
                    'prop_value2' => 'new local value to remote',
                    'prop3' => 'prop3Value local'
                ],
                'remoteData' => [
                    'prop_value' => 'new remote value',
                    'prop_value2' => 'old local value to remote',
                    'prop3' => 'prop3Value'
                ],
                'strategy' => 'local',
                'expected' => [
                    'prop_value' => 'value',
                    'prop_value2' => 'new local value to remote',
                    'prop3' => 'prop3Value local'
                ],
                'additionalFields' => ['prop3']
            ],
            'additional boolean field local wins' => [
                'changeSet'  => [
                    'propValue' => ['old' => false, 'new' => 1],
                ],
                'localData'  => [
                    'prop_value' => '1',
                ],
                'remoteData' => [
                    'prop_value' => '0',
                ],
                'strategy'   => 'local',
                'expected'   => [
                    'prop_value'  => 1,
                ],
            ],
            'additional boolean field remote wins' => [
                'changeSet'  => [
                    'propValue' => ['old' => false, 'new' => 1],
                ],
                'localData'  => [
                    'prop_value' => '1',
                ],
                'remoteData' => [
                    'prop_value' => '0',
                ],
                'strategy'   => 'remote',
                'expected'   => [
                    'prop_value'  => 1,
                ],
            ],
        ];
    }
}
