<?php

namespace Oro\Bundle\CaseBundle\Tests\Unit\EventListener;

use Doctrine\Common\Collections\Collection;
use Oro\Bundle\CaseBundle\Entity\CaseMailboxProcessSettings;
use Oro\Bundle\CaseBundle\EventListener\MailboxSavedListener;
use Oro\Bundle\EmailBundle\Entity\Mailbox;
use Oro\Bundle\EmailBundle\Event\MailboxSaved;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\TagBundle\Entity\TagManager;

class MailboxSavedListenerTest extends \PHPUnit\Framework\TestCase
{
    /** @var TagManager|\PHPUnit\Framework\MockObject\MockObject */
    private $tagManager;

    /** @var MailboxSavedListener */
    private $listener;

    protected function setUp(): void
    {
        $this->tagManager = $this->createMock(TagManager::class);

        $this->listener = new MailboxSavedListener($this->tagManager);
    }

    public function testOnMailboxSave()
    {
        $event = $this->createMock(MailboxSaved::class);

        $mailbox = $this->createMock(Mailbox::class);

        $settings = $this->createMock(CaseMailboxProcessSettings::class);

        $mailbox->expects(self::once())
            ->method('getProcessSettings')
            ->willReturn($settings);

        $event->expects(self::once())
            ->method('getMailbox')
            ->willReturn($mailbox);

        $organization = $this->createMock(Organization::class);

        $mailbox->expects(self::once())
            ->method('getOrganization')
            ->willReturn($organization);

        $tags = $this->createMock(Collection::class);

        $settings->expects(self::once())
            ->method('getTags')
            ->willReturn($tags);

        $this->tagManager->expects(self::once())
            ->method('setTags')
            ->with($settings, $tags);

        $this->tagManager->expects(self::once())
            ->method('saveTagging')
            ->with($settings, true, $organization);

        $this->listener->onMailboxSave($event);
    }

    public function testOnMailboxSaveNotCaseSettings()
    {
        $event = $this->createMock(MailboxSaved::class);

        $mailbox = $this->createMock(Mailbox::class);

        $mailbox->expects(self::once())
            ->method('getProcessSettings')
            ->willReturn(new \stdClass());

        $event->expects(self::once())
            ->method('getMailbox')
            ->willReturn($mailbox);

        $this->tagManager->expects(self::never())
            ->method('saveTagging');

        $this->listener->onMailboxSave($event);
    }
}
