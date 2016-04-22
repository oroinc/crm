<?php

namespace OroCRM\Bundle\ContactBundle\Tests\Unit\EventListener;

use Doctrine\Common\Persistence\ObjectManager;

use Oro\Bundle\SearchBundle\Event\PrepareResultItemEvent;
use Oro\Bundle\SearchBundle\Query\Result\Item;

use OroCRM\Bundle\ContactBundle\Entity\Contact;
use OroCRM\Bundle\ContactBundle\Entity\ContactEmail;
use OroCRM\Bundle\ContactBundle\Entity\ContactPhone;
use OroCRM\Bundle\ContactBundle\EventListener\PrepareResultItemListener;

class PrepareResultItemListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider prepareEmailItemDataEventProvider
     */
    public function testPrepareEmailItemDataEvent($event, $expectedEvent)
    {
        $listener = new PrepareResultItemListener();
        $listener->prepareEmailItemDataEvent($event);

        $this->assertEquals($expectedEvent, $event);
    }

    public function prepareEmailItemDataEventProvider()
    {
        return [
            'contact with first name' => [
                new PrepareResultItemEvent(
                    new Item(
                        $this->getObjectManager(
                            (new Contact())
                                ->setFirstName('first')
                        ),
                        'OroCRM\Bundle\ContactBundle\Entity\Contact',
                        1
                    )
                ),
                new PrepareResultItemEvent(
                    new Item(
                        $this->getObjectManager(
                            (new Contact())
                                ->setFirstName('first')
                        ),
                        'OroCRM\Bundle\ContactBundle\Entity\Contact',
                        1
                    )
                ),
            ],
            'contact with last name' => [
                new PrepareResultItemEvent(
                    new Item(
                        $this->getObjectManager(
                            (new Contact())
                                ->setLastName('last')
                        ),
                        'OroCRM\Bundle\ContactBundle\Entity\Contact',
                        1
                    )
                ),
                new PrepareResultItemEvent(
                    new Item(
                        $this->getObjectManager(
                            (new Contact())
                                ->setLastName('last')
                        ),
                        'OroCRM\Bundle\ContactBundle\Entity\Contact',
                        1
                    )
                ),
            ],
            'contact with first and last names' => [
                new PrepareResultItemEvent(
                    new Item(
                        $this->getObjectManager(
                            (new Contact())
                                ->setFirstName('first')
                                ->setLastName('last')
                        ),
                        'OroCRM\Bundle\ContactBundle\Entity\Contact',
                        1
                    )
                ),
                new PrepareResultItemEvent(
                    new Item(
                        $this->getObjectManager(
                            (new Contact())
                                ->setFirstName('first')
                                ->setLastName('last')
                        ),
                        'OroCRM\Bundle\ContactBundle\Entity\Contact',
                        1
                    )
                ),
            ],
            'contact with email' => [
                new PrepareResultItemEvent(
                    new Item(
                        $this->getObjectManager(
                            (new Contact())
                                ->addEmail((new ContactEmail('contact@example.com'))->setPrimary(true))
                        ),
                        'OroCRM\Bundle\ContactBundle\Entity\Contact',
                        1
                    )
                ),
                new PrepareResultItemEvent(
                    new Item(
                        $this->getObjectManager(
                            (new Contact())
                                ->addEmail((new ContactEmail('contact@example.com'))->setPrimary(true))
                        ),
                        'OroCRM\Bundle\ContactBundle\Entity\Contact',
                        1,
                        'contact@example.com'
                    )
                ),
            ],
            'contact with phone' => [
                new PrepareResultItemEvent(
                    new Item(
                        $this->getObjectManager(
                            (new Contact())
                                ->addPhone((new ContactPhone('5432345'))->setPrimary(true))
                        ),
                        'OroCRM\Bundle\ContactBundle\Entity\Contact',
                        1
                    )
                ),
                new PrepareResultItemEvent(
                    new Item(
                        $this->getObjectManager(
                            (new Contact())
                                ->addPhone((new ContactPhone('5432345'))->setPrimary(true))
                        ),
                        'OroCRM\Bundle\ContactBundle\Entity\Contact',
                        1,
                        '5432345'
                    )
                ),
            ],
            'contact with phone and email' => [
                new PrepareResultItemEvent(
                    new Item(
                        $this->getObjectManager(
                            (new Contact())
                                ->addPhone((new ContactPhone('5432345'))->setPrimary(true))
                                ->addEmail((new ContactEmail('contact@example.com'))->setPrimary(true))
                        ),
                        'OroCRM\Bundle\ContactBundle\Entity\Contact',
                        1
                    )
                ),
                new PrepareResultItemEvent(
                    new Item(
                        $this->getObjectManager(
                            (new Contact())
                                ->addPhone((new ContactPhone('5432345'))->setPrimary(true))
                                ->addEmail((new ContactEmail('contact@example.com'))->setPrimary(true))
                        ),
                        'OroCRM\Bundle\ContactBundle\Entity\Contact',
                        1,
                        '5432345'
                    )
                ),
            ],
        ];
    }
    

    /**
     * @param object $entity
     *
     * @return ObjectManager
     */
    protected function getObjectManager($entity)
    {
        $repository = $this->getMock('Doctrine\Common\Persistence\ObjectRepository');
        $repository
            ->expects($this->any())
            ->method('find')
            ->will($this->returnValue($entity));

        $om = $this->getMock('Doctrine\Common\Persistence\ObjectManager');
        $om->expects($this->any())
            ->method('getRepository')
            ->will($this->returnValue($repository));

        return $om;
    }
}
