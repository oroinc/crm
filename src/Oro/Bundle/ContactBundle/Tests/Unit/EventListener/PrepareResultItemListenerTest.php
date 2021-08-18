<?php

namespace Oro\Bundle\ContactBundle\Tests\Unit\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\ContactBundle\Entity\ContactPhone;
use Oro\Bundle\ContactBundle\EventListener\PrepareResultItemListener;
use Oro\Bundle\ContactBundle\Formatter\ContactNameFormatter;
use Oro\Bundle\SearchBundle\Event\PrepareResultItemEvent;
use Oro\Bundle\SearchBundle\Query\Result\Item;

class PrepareResultItemListenerTest extends \PHPUnit\Framework\TestCase
{
    /** @var ContactNameFormatter|\PHPUnit\Framework\MockObject\MockObject */
    private $nameFormatter;

    /** @var ManagerRegistry|\PHPUnit\Framework\MockObject\MockObject */
    private $doctrine;

    /** @var PrepareResultItemListener */
    private $listener;

    protected function setUp(): void
    {
        $this->nameFormatter = $this->createMock(ContactNameFormatter::class);
        $this->doctrine = $this->createMock(ManagerRegistry::class);

        $this->listener = new PrepareResultItemListener($this->nameFormatter, $this->doctrine);
    }

    public function testPrepareResultItemWithoutTitle()
    {
        $item = new Item(Contact::class, 1);
        $entity = (new Contact())->setFirstName('first');
        $expectedTitle = 'first';

        $em = $this->createMock(EntityManagerInterface::class);
        $em->expects($this->once())
            ->method('find')
            ->with(Contact::class, $item->getId())
            ->willReturn($entity);

        $this->doctrine->expects($this->once())
            ->method('getManagerForClass')
            ->with(Contact::class)
            ->willReturn($em);

        $this->nameFormatter->expects($this->once())
            ->method('format')
            ->willReturnCallback(function (Contact $contact) {
                return trim(implode(' ', [$contact->getFirstName(), $contact->getLastName()]));
            });

        $event = new PrepareResultItemEvent($item);
        $this->listener->prepareResultItem($event);

        $this->assertSame($expectedTitle, $event->getResultItem()->getRecordTitle());
    }

    public function testPrepareResultItemWithTitle()
    {
        $item = new Item(Contact::class, 1, 'preset title');
        $expectedTitle = $item->getRecordTitle();

        $this->doctrine->expects($this->never())
            ->method('getManagerForClass');

        $this->nameFormatter->expects($this->never())
            ->method('format');

        $event = new PrepareResultItemEvent($item);
        $this->listener->prepareResultItem($event);

        $this->assertSame($expectedTitle, $event->getResultItem()->getRecordTitle());
    }

    /**
     * @dataProvider prepareResultItemForNotSupportedEntityDataProvider
     */
    public function testPrepareResultItemForNotSupportedEntity(Item $item)
    {
        $expectedTitle = $item->getRecordTitle();

        $this->doctrine->expects($this->never())
            ->method('getManagerForClass');

        $this->nameFormatter->expects($this->never())
            ->method('format');

        $event = new PrepareResultItemEvent($item);
        $this->listener->prepareResultItem($event);

        $this->assertSame($expectedTitle, $event->getResultItem()->getRecordTitle());
    }

    public function prepareResultItemForNotSupportedEntityDataProvider(): array
    {
        return [
            'without title' => [
                new Item(ContactPhone::class, 1)
            ],
            'with title'    => [
                new Item(ContactPhone::class, 1, 'preset title')
            ]
        ];
    }
}
