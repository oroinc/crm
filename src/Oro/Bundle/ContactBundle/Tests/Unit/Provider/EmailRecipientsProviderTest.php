<?php

namespace Oro\Bundle\ContactBundle\Tests\Unit\Provider;

use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\ContactBundle\Entity\Repository\ContactRepository;
use Oro\Bundle\ContactBundle\Provider\EmailRecipientsProvider;
use Oro\Bundle\EmailBundle\Model\EmailRecipientsProviderArgs;
use Oro\Bundle\EmailBundle\Provider\EmailRecipientsHelper;

class EmailRecipientsProviderTest extends \PHPUnit\Framework\TestCase
{
    /** @var ManagerRegistry|\PHPUnit\Framework\MockObject\MockObject */
    private $doctrine;

    /** @var EmailRecipientsHelper|\PHPUnit\Framework\MockObject\MockObject */
    private $emailRecipientsHelper;

    /** @var EmailRecipientsProvider */
    private $emailRecipientsProvider;

    protected function setUp(): void
    {
        $this->doctrine = $this->createMock(ManagerRegistry::class);
        $this->emailRecipientsHelper = $this->createMock(EmailRecipientsHelper::class);

        $this->emailRecipientsProvider = new EmailRecipientsProvider(
            $this->doctrine,
            $this->emailRecipientsHelper
        );
    }

    /**
     * @dataProvider dataProvider
     */
    public function testGetRecipients(EmailRecipientsProviderArgs $args, array $recipients)
    {
        $contactRepository = $this->createMock(ContactRepository::class);

        $this->doctrine->expects($this->once())
            ->method('getRepository')
            ->with(Contact::class)
            ->willReturn($contactRepository);

        $this->emailRecipientsHelper->expects($this->once())
            ->method('getRecipients')
            ->with($args, $contactRepository, 'c', Contact::class)
            ->willReturn($recipients);

        $this->assertEquals($recipients, $this->emailRecipientsProvider->getRecipients($args));
    }

    public function dataProvider(): array
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
