<?php

namespace OroCRM\Bundle\ContactBundle\Tests\Unit\Provider;

use Oro\Bundle\EmailBundle\Model\EmailRecipientsProviderArgs;

use OroCRM\Bundle\ContactBundle\Provider\EmailRecipientsProvider;

class EmailRecipientsProviderTest extends \PHPUnit_Framework_TestCase
{
    protected $registry;
    protected $emailRecipientsHelper;

    protected $emailRecipientsProvider;

    public function setUp()
    {
        $this->registry = $this->getMockBuilder('Doctrine\Bundle\DoctrineBundle\Registry')
            ->disableOriginalConstructor()
            ->getMock();

        $this->emailRecipientsHelper = $this->getMockBuilder('Oro\Bundle\EmailBundle\Provider\EmailRecipientsHelper')
            ->disableOriginalConstructor()
            ->getMock();

        $this->emailRecipientsProvider = new EmailRecipientsProvider(
            $this->registry,
            $this->emailRecipientsHelper
        );
    }

    /**
     * @dataProvider dataProvider
     */
    public function testGetRecipients(EmailRecipientsProviderArgs $args, array $recipients)
    {
        $contactRepository = $this->getMockBuilder('OroCRM\Bundle\ContactBundle\Entity\Repository\ContactRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $this->registry->expects($this->once())
            ->method('getRepository')
            ->with('OroCRMContactBundle:Contact')
            ->will($this->returnValue($contactRepository));

        $this->emailRecipientsHelper->expects($this->once())
            ->method('getRecipients')
            ->with($args, $contactRepository, 'c', 'OroCRM\Bundle\ContactBundle\Entity\Contact')
            ->will($this->returnValue($recipients));

        $this->assertEquals($recipients, $this->emailRecipientsProvider->getRecipients($args));
    }

    public function dataProvider()
    {
        return [
            [
                new EmailRecipientsProviderArgs(null, null, 1),
                [
                    'recipient@example.com'  => 'Recipient <recipient@example.com>',
                ],
            ],
        ];
    }
}
