<?php

namespace Oro\Bundle\CaseBundle\Tests\Unit\EventListener;

use Oro\Bundle\SearchBundle\Engine\ObjectMapper;
use Oro\Bundle\SearchBundle\Event\PrepareEntityMapEvent;

use Oro\Bundle\CaseBundle\Entity\CaseEntity;
use Oro\Bundle\CaseBundle\EventListener\SearchIndexDataListener;

class SearchIndexDataListenerTest extends \PHPUnit_Framework_TestCase
{
    /** @var ObjectMapper|\PHPUnit_Framework_MockObject_MockObject */
    protected $mapper;

    /** @var SearchIndexDataListener */
    protected $listener;

    protected function setUp()
    {
        $this->mapper = $this->getMockBuilder(ObjectMapper::class)
            ->disableOriginalConstructor()
            ->setMethodsExcept(['buildAllDataField'])
            ->getMock();

        $this->listener = new SearchIndexDataListener($this->mapper);
    }

    public function testOnPrepareEntityMapNotCaseEntity()
    {
        $data = ['message' => 'very long string'];
        $event = new PrepareEntityMapEvent(new \DateTime(), \DateTime::class, $data, []);

        $this->listener->setShortenedFields(['message' => 4]);
        $this->listener->onPrepareEntityMap($event);

        $this->assertEquals($data, $event->getData());
    }

    /**
     * @param array $shortenedFields
     * @param array $originalData
     * @param array $expectedData
     * @dataProvider onPrepareEntityMapDataProvider
     */
    public function testOnPrepareEntityMap(array $shortenedFields, array $originalData, array $expectedData)
    {
        $event = new PrepareEntityMapEvent(new CaseEntity(), CaseEntity::class, $originalData, []);

        $this->listener->setShortenedFields($shortenedFields);
        $this->listener->onPrepareEntityMap($event);

        $this->assertEquals($expectedData, $event->getData());
    }

    /**
     * @return array
     */
    public function onPrepareEntityMapDataProvider()
    {
        return [
            'nothing to change' => [
                'shortenedFields' => ['description' => 255, 'message' => 255],
                'originalData' => [
                    'text' => [
                        'description' => 'some description',
                        'message' => 'some message',
                        'all_text' => ' some description message'
                    ]
                ],
                'expectedData' => [
                    'text' => [
                        'description' => 'some description',
                        'message' => 'some message',
                        'all_text' => ' some description message'
                    ]
                ],
            ],
            'one field shortened' => [
                'shortenedFields' => ['description' => 255, 'message' => 4],
                'originalData' => [
                    'text' => [
                        'description' => 'some description',
                        'message' => 'some message',
                        'all_text' => ' some description message'
                    ]
                ],
                'expectedData' => [
                    'text' => [
                        'description' => 'some description',
                        'message' => 'some',
                        'all_text' => ' some description'
                    ]
                ],
            ],
            'two fields shortened' => [
                'shortenedFields' => ['description' => 10, 'message' => 4],
                'originalData' => [
                    'text' => [
                        'description' => 'some description',
                        'message' => 'some message',
                        'all_text' => ' some description message'
                    ]
                ],
                'expectedData' => [
                    'text' => [
                        'description' => 'some descr',
                        'message' => 'some',
                        'all_text' => ' some descr'
                    ]
                ],
            ],
        ];
    }
}
