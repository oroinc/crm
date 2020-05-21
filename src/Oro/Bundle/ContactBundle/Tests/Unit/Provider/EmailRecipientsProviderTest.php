<?php

namespace Oro\Bundle\ContactBundle\Tests\Unit\Provider;

use Oro\Bundle\ContactBundle\Provider\EmailRecipientsProvider;
use Oro\Bundle\EmailBundle\Model\EmailRecipientsProviderArgs;

class EmailRecipientsProviderTest extends \PHPUnit\Framework\TestCase
{
    protected $registry;
    protected $emailRecipientsHelper;

    protected $emailRecipientsProvider;

    protected function setUp(): void
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
        $contactRepository = $this->getMockBuilder('Oro\Bundle\ContactBundle\Entity\Repository\ContactRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $this->registry->expects($this->once())
            ->method('getRepository')
            ->with('OroContactBundle:Contact')
            ->will($this->returnValue($contactRepository));

        $this->emailRecipientsHelper->expects($this->once())
            ->method('getRecipients')
            ->with($args, $contactRepository, 'c', 'Oro\Bundle\ContactBundle\Entity\Contact')
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
