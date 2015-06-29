<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Unit\EventListener;

use Oro\Bundle\EmailBundle\Event\EmailRecipientsLoadEvent;
use OroCRM\Bundle\AccountBundle\Entity\Account;
use OroCRM\Bundle\MagentoBundle\Entity\Customer;
use OroCRM\Bundle\MagentoBundle\Entity\Order;
use OroCRM\Bundle\MagentoBundle\EventListener\EmailRecipientsLoadListener;

class EmailRecipientsLoadListenerTest extends \PHPUnit_Framework_TestCase
{
    protected $registry;
    protected $relatedEmailsProvider;
    protected $emailRecipientsHelper;

    protected $emailRecipientsLoadListener;

    public function setUp()
    {
        $this->registry = $this->getMockBuilder('Doctrine\Bundle\DoctrineBundle\Registry')
            ->disableOriginalConstructor()
            ->getMock();

        $this->relatedEmailsProvider = $this->getMockBuilder('Oro\Bundle\EmailBundle\Provider\RelatedEmailsProvider')
            ->disableOriginalConstructor()
            ->getMock();

        $this->emailRecipientsHelper = $this->getMockBuilder('Oro\Bundle\EmailBundle\Provider\EmailRecipientsHelper')
            ->disableOriginalConstructor()
            ->getMock();

        $this->emailRecipientsLoadListener = new EmailRecipientsLoadListener(
            $this->registry,
            $this->relatedEmailsProvider,
            $this->emailRecipientsHelper
        );
    }

    public function testListenerShouldNotAddStuffInContextIfLimitIsNotPositive()
    {
        $query = 'query';
        $limit = 0;

        $this->emailRecipientsHelper->expects($this->never())
            ->method('addEmailsToContext');

        $event = new EmailRecipientsLoadEvent(null, $query, $limit);
        $this->emailRecipientsLoadListener->onLoad($event);
    }

    public function testListenerShouldNotAddStuffInContextIfRelatedEntityIsNotAccount()
    {
        $query = 'query';
        $limit = 1;

        $this->emailRecipientsHelper->expects($this->never())
            ->method('addEmailsToContext');

        $relatedEntity = new Order();
        $event = new EmailRecipientsLoadEvent($relatedEntity, $query, $limit);
        $this->emailRecipientsLoadListener->onLoad($event);
    }

    public function testListenerShouldAddStuffInContextIfRelatedEntityIsAccount()
    {
        $query = 'query';
        $limit = 1;

        $emails = [
            'mail@example.com' => 'Mail <mail@example.com>',
        ];

        $customer = new Customer();
        $customers = [$customer];

        $relatedEntity = new Account();
        $event = new EmailRecipientsLoadEvent($relatedEntity, $query, $limit);

        $customerRepository = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $customerRepository->expects($this->once())
            ->method('findBy')
            ->with(['account' => $relatedEntity])
            ->will($this->returnValue($customers));

        $this->registry->expects($this->once())
            ->method('getRepository')
            ->with('OroCRMMagentoBundle:Customer')
            ->will($this->returnValue($customerRepository));

        $this->relatedEmailsProvider->expects($this->once())
            ->method('getEmails')
            ->with($customer, 2)
            ->will($this->returnValue($emails));

        $this->emailRecipientsHelper->expects($this->once())
            ->method('addEmailsToContext')
            ->with($event, $emails);

        $this->emailRecipientsLoadListener->onLoad($event);
    }
}
