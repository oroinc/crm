<?php

namespace OroCRM\Bundle\ContactBundle\Tests\Unit\EventListener;

use Oro\Bundle\EmailBundle\Event\EmailRecipientsLoadEvent;
use OroCRM\Bundle\ContactBundle\EventListener\EmailRecipientsLoadListener;

class EmailRecipientsLoadListenerTest extends \PHPUnit_Framework_TestCase
{
    protected $registry;
    protected $aclHelper;
    protected $translator;

    protected $emailRecipientsLoadListener;

    public function setUp()
    {
        $this->registry = $this->getMockBuilder('Doctrine\Bundle\DoctrineBundle\Registry')
            ->disableOriginalConstructor()
            ->getMock();

        $this->aclHelper = $this->getMockBuilder('Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper')
            ->disableOriginalConstructor()
            ->getMock();

        $this->translator = $this->getMock('Symfony\Component\Translation\TranslatorInterface');
        $this->translator->expects($this->any())
            ->method('trans')
            ->will($this->returnCallback(function ($id) {
                return $id;
            }));

        $this->emailRecipientsLoadListener = new EmailRecipientsLoadListener(
            $this->registry,
            $this->aclHelper,
            $this->translator
        );
    }

    public function testOnLoadShouldSetNothingIfLimitIsNotPositive()
    {
        $query = 'query';
        $limit = 0;

        $expectedResults = [];

        $event = new EmailRecipientsLoadEvent(null, $query, $limit);
        $this->emailRecipientsLoadListener->onLoad($event);
        
        $this->assertEquals($expectedResults, $event->getResults());
    }

    public function testOnLoadShouldSetNothingIfRepositoryReturnsEmptyResult()
    {
        $query = 'query';
        $limit = 1;

        $expectedResults = [];

        $contactRepository = $this->getMockBuilder('OroCRM\Bundle\ContactBundle\Entity\Repository\ContactRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $contactRepository->expects($this->once())
            ->method('getEmails')
            ->with($this->aclHelper, [], $query, $limit)
            ->will($this->returnValue([]));

        $this->registry->expects($this->once())
            ->method('getRepository')
            ->with('OroCRMContactBundle:Contact')
            ->will($this->returnValue($contactRepository));

        $event = new EmailRecipientsLoadEvent(null, $query, $limit);
        $this->emailRecipientsLoadListener->onLoad($event);
        
        $this->assertEquals($expectedResults, $event->getResults());
    }

    public function testOnLoadShouldSetResultsReturnedByRepository()
    {
        $query = 'query';
        $limit = 1;

        $expectedResults = [
            [
                'text' => 'orocrm.contact.entity_plural_label',
                'children' => [
                    [
                        'id'   => 'query@example.com',
                        'text' => 'Name <query@example.com>',
                    ],
                ],
            ]
        ];

        $contactRepository = $this->getMockBuilder('OroCRM\Bundle\ContactBundle\Entity\Repository\ContactRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $contactRepository->expects($this->once())
            ->method('getEmails')
            ->with($this->aclHelper, [], $query, $limit)
            ->will($this->returnValue([
                'query@example.com' => 'Name <query@example.com>',
            ]));

        $this->registry->expects($this->once())
            ->method('getRepository')
            ->with('OroCRMContactBundle:Contact')
            ->will($this->returnValue($contactRepository));

        $event = new EmailRecipientsLoadEvent(null, $query, $limit);
        $this->emailRecipientsLoadListener->onLoad($event);
        
        $this->assertEquals($expectedResults, $event->getResults());
    }
}
