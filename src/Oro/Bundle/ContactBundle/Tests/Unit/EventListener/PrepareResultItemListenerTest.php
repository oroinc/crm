<?php

namespace Oro\Bundle\ContactBundle\Tests\Unit\EventListener;

use Doctrine\Persistence\ObjectRepository;
use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\ContactBundle\Entity\ContactPhone;
use Oro\Bundle\ContactBundle\EventListener\PrepareResultItemListener;
use Oro\Bundle\ContactBundle\Formatter\ContactNameFormatter;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\SearchBundle\Event\PrepareResultItemEvent;
use Oro\Bundle\SearchBundle\Query\Result\Item;

class PrepareResultItemListenerTest extends \PHPUnit\Framework\TestCase
{
    /** @var ContactNameFormatter */
    protected $nameFormatter;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->nameFormatter = $this->getMockBuilder('Oro\Bundle\ContactBundle\Formatter\ContactNameFormatter')
            ->disableOriginalConstructor()
            ->getMock();

        $this->nameFormatter->expects($this->any())
            ->method('format')
            ->will($this->returnCallback(function (Contact $contact) {
                return trim(implode(' ', [$contact->getFirstName(), $contact->getLastName()]));
            }));
    }

    /**
     * @dataProvider prepareEmailItemDataEventProvider
     */
    public function testPrepareEmailItemDataEvent(
        PrepareResultItemEvent $event,
        PrepareResultItemEvent $expectedEvent,
        DoctrineHelper $doctrineHelper
    ) {
        $listener = new PrepareResultItemListener($this->nameFormatter, $doctrineHelper);
        $listener->prepareEmailItemDataEvent($event);

        $this->assertEquals($expectedEvent, $event);
    }

    /**
     * @return array
     */
    public function prepareEmailItemDataEventProvider()
    {
        return [
            'event with contact without title' => [
                new PrepareResultItemEvent(
                    new Item(
                        'Oro\Bundle\ContactBundle\Entity\Contact',
                        1
                    )
                ),
                new PrepareResultItemEvent(
                    new Item(
                        'Oro\Bundle\ContactBundle\Entity\Contact',
                        1,
                        'first'
                    )
                ),
                $this->getDoctrineHelper(
                    (new Contact())
                        ->setFirstName('first')
                ),
            ],
            'event with contact with title' => [
                new PrepareResultItemEvent(
                    new Item(
                        'Oro\Bundle\ContactBundle\Entity\Contact',
                        1,
                        'preset title'
                    )
                ),
                new PrepareResultItemEvent(
                    new Item(
                        'Oro\Bundle\ContactBundle\Entity\Contact',
                        1,
                        'preset title'
                    )
                ),
                $this->getDoctrineHelper(
                    (new Contact())
                        ->setFirstName('first')
                ),
            ],
            'event without contact without title' => [
                new PrepareResultItemEvent(
                    new Item(
                        'Oro\Bundle\ContactBundle\Entity\ContactPhone',
                        1
                    )
                ),
                new PrepareResultItemEvent(
                    new Item(
                        'Oro\Bundle\ContactBundle\Entity\ContactPhone',
                        1
                    )
                ),
                $this->getDoctrineHelper(
                    (new ContactPhone())
                        ->setPhone('53582379475')
                ),
            ],
            'event without contact with title' => [
                new PrepareResultItemEvent(
                    new Item(
                        'Oro\Bundle\ContactBundle\Entity\ContactPhone',
                        1,
                        'preset title'
                    )
                ),
                new PrepareResultItemEvent(
                    new Item(
                        'Oro\Bundle\ContactBundle\Entity\ContactPhone',
                        1,
                        'preset title'
                    )
                ),
                $this->getDoctrineHelper(
                    (new ContactPhone())
                        ->setPhone('53582379475')
                ),
            ],
        ];
    }

    /**
     * @param object $entity
     *
     * @return DoctrineHelper
     */
    protected function getDoctrineHelper($entity)
    {
        $repository = $this->createMock(ObjectRepository::class);
        $repository
            ->expects($this->any())
            ->method('find')
            ->will($this->returnValue($entity));

        /** @var DoctrineHelper|\PHPUnit\Framework\MockObject\MockObject $doctrineHelper */
        $doctrineHelper = $this->getMockBuilder(DoctrineHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $doctrineHelper->expects($this->any())
            ->method('getEntityRepository')
            ->will($this->returnValue($repository));

        return $doctrineHelper;
    }
}
