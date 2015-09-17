<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Unit\Provider;

use Oro\Bundle\EmailBundle\Model\EmailRecipientsProviderArgs;
use Oro\Bundle\EmailBundle\Model\Recipient;

use OroCRM\Bundle\AccountBundle\Entity\Account;
use OroCRM\Bundle\MagentoBundle\Entity\Customer;
use OroCRM\Bundle\MagentoBundle\Entity\Order;
use OroCRM\Bundle\MagentoBundle\Provider\EmailRecipientsProvider;

class EmailRecipientsProviderTest extends \PHPUnit_Framework_TestCase
{
    protected $registry;
    protected $relatedEmailsProvider;

    protected $emailRecipientsProvider;

    public function setUp()
    {
        $this->registry = $this->getMockBuilder('Doctrine\Bundle\DoctrineBundle\Registry')
            ->disableOriginalConstructor()
            ->getMock();

        $this->relatedEmailsProvider = $this->getMockBuilder('Oro\Bundle\EmailBundle\Provider\RelatedEmailsProvider')
            ->disableOriginalConstructor()
            ->getMock();

        $this->emailRecipientsProvider = new EmailRecipientsProvider(
            $this->registry,
            $this->relatedEmailsProvider
        );
    }

    public function testGetRecipientsShouldNotAddStuffInContextIfRelatedEntityIsNotAccount()
    {
        $args = new EmailRecipientsProviderArgs(new Order(), 're', 100);
        $this->assertEmpty($this->emailRecipientsProvider->getRecipients($args));
    }

    /**
     * @dataProvider dataProvider
     */
    public function testGetRecipients(EmailRecipientsProviderArgs $args, array $expectedEmails)
    {
        $customer = new Customer();
        $customers = [$customer];

        $customerRepository = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $customerRepository->expects($this->once())
            ->method('findBy')
            ->with(['account' => $args->getRelatedEntity()])
            ->will($this->returnValue($customers));

        $this->registry->expects($this->once())
            ->method('getRepository')
            ->with('OroCRMMagentoBundle:Customer')
            ->will($this->returnValue($customerRepository));

        $this->relatedEmailsProvider->expects($this->once())
            ->method('getRecipients')
            ->with($customer, 2)
            ->will($this->returnValue($expectedEmails));

        $this->assertEquals($expectedEmails, $this->emailRecipientsProvider->getRecipients($args));
    }

    public function dataProvider()
    {
        return [
            [
                new EmailRecipientsProviderArgs(new Account(), 're', 100),
                [
                    new Recipient('recipient@example.com', 'Recipient <recipient@example.com>'),
                    new Recipient('recipient2@example.com', 'Recipient2 <recipient2@example.com>'),
                ]
            ],
        ];
    }
}
