<?php

namespace Oro\Bundle\CaseBundle\Tests\Unit\EventListener;

use Oro\Bundle\CaseBundle\Entity\CaseEntity;
use Oro\Bundle\CaseBundle\EventListener\SearchIndexDataListener;
use Oro\Bundle\SearchBundle\Engine\ObjectMapper;
use Oro\Bundle\SearchBundle\Event\PrepareEntityMapEvent;
use Oro\Bundle\SearchBundle\Provider\SearchMappingProvider;
use Oro\Bundle\SearchBundle\Test\Unit\SearchMappingTypeCastingHandlersTestTrait;
use Oro\Bundle\UIBundle\Tools\HtmlTagHelper;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;

class SearchIndexDataListenerTest extends \PHPUnit\Framework\TestCase
{
    use SearchMappingTypeCastingHandlersTestTrait;

    /** @var ObjectMapper|\PHPUnit\Framework\MockObject\MockObject */
    protected $mapper;

    /** @var SearchIndexDataListener */
    protected $listener;

    protected function setUp(): void
    {
        /** @var HtmlTagHelper|\PHPUnit\Framework\MockObject\MockObject $htmlTagHelper */
        $htmlTagHelper = $this->createMock(HtmlTagHelper::class);
        $htmlTagHelper->expects($this->any())
            ->method('stripTags')
            ->willReturnCallback(
                function ($value) {
                    return trim(strip_tags($value));
                }
            );
        $htmlTagHelper->expects($this->any())
            ->method('stripLongWords')
            ->willReturnCallback(
                function ($value) {
                    $words = preg_split('/\s+/', $value);

                    $words = array_filter(
                        $words,
                        function ($item) {
                            return \strlen($item) <= HtmlTagHelper::MAX_STRING_LENGTH;
                        }
                    );

                    return implode(' ', $words);
                }
            );

        $this->mapper = new ObjectMapper(
            $this->createMock(SearchMappingProvider::class),
            PropertyAccess::createPropertyAccessor(),
            $this->createMock(EventDispatcherInterface::class),
            $htmlTagHelper
        );
        $this->mapper->setTypeCastingHandlerRegistry($this->getTypeCastingHandlerRegistry());

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
                        'all_text' => 'some description'
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
                        'all_text' => 'some descr'
                    ]
                ],
            ],
        ];
    }
}
