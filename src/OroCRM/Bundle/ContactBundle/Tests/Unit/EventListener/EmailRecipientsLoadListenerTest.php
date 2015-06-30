<?php

namespace OroCRM\Bundle\ContactBundle\Tests\Unit\EventListener;

use Oro\Bundle\EmailBundle\Event\EmailRecipientsLoadEvent;
use OroCRM\Bundle\ContactBundle\EventListener\EmailRecipientsLoadListener;

class EmailRecipientsLoadListenerTest extends \PHPUnit_Framework_TestCase
{
    protected $nameFormatter;
    protected $registry;
    protected $aclHelper;
    protected $translator;
    protected $emailRecipientsHelper;

    protected $emailRecipientsLoadListener;

    public function setUp()
    {
        $this->nameFormatter = $this->getMockBuilder('Oro\Bundle\LocaleBundle\DQL\DQLNameFormatter')
            ->disableOriginalConstructor()
            ->getMock();

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

        $this->emailRecipientsHelper = $this->getMockBuilder('Oro\Bundle\EmailBundle\Provider\EmailRecipientsHelper')
            ->disableOriginalConstructor()
            ->getMock();

        $this->emailRecipientsLoadListener = new EmailRecipientsLoadListener(
            $this->registry,
            $this->aclHelper,
            $this->translator,
            $this->emailRecipientsHelper,
            $this->nameFormatter
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

        $fullNameQueryPart = 'c.firstName';

        $expectedResults = [];

        $this->nameFormatter->expects($this->once())
            ->method('getFormattedNameDQL')
            ->with('c', 'OroCRM\Bundle\ContactBundle\Entity\Contact')
            ->will($this->returnValue($fullNameQueryPart));

        $contactRepository = $this->getMockBuilder('OroCRM\Bundle\ContactBundle\Entity\Repository\ContactRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $contactRepository->expects($this->once())
            ->method('getEmails')
            ->with($this->aclHelper, $fullNameQueryPart, [], $query, $limit)
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

        $fullNameQueryPart = 'c.firstName';

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

        $this->nameFormatter->expects($this->once())
            ->method('getFormattedNameDQL')
            ->with('c', 'OroCRM\Bundle\ContactBundle\Entity\Contact')
            ->will($this->returnValue($fullNameQueryPart));

        $contactRepository = $this->getMockBuilder('OroCRM\Bundle\ContactBundle\Entity\Repository\ContactRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $contactRepository->expects($this->once())
            ->method('getEmails')
            ->with($this->aclHelper, $fullNameQueryPart, [], $query, $limit)
            ->will($this->returnValue([
                'query@example.com' => 'Name <query@example.com>',
            ]));

        $this->registry->expects($this->once())
            ->method('getRepository')
            ->with('OroCRMContactBundle:Contact')
            ->will($this->returnValue($contactRepository));

        $this->emailRecipientsHelper->expects($this->once())
            ->method('createResultFromEmails')
            ->with([
                'query@example.com' => 'Name <query@example.com>',
            ])
            ->will($this->returnValue($expectedResults[0]['children']));

        $event = new EmailRecipientsLoadEvent(null, $query, $limit);
        $this->emailRecipientsLoadListener->onLoad($event);
        
        $this->assertEquals($expectedResults, $event->getResults());
    }
}
