<?php

namespace Oro\Bundle\CaseBundle\EventListener;

use Oro\Bundle\CaseBundle\Entity\CaseMailboxProcessSettings;
use Oro\Bundle\EmailBundle\Event\MailboxSaved;
use Oro\Bundle\TagBundle\Entity\TagManager;

class MailboxSavedListener
{
    /** @var TagManager */
    private $tagManager;

    public function __construct(TagManager $tagManager)
    {
        $this->tagManager = $tagManager;
    }

    public function onMailboxSave(MailboxSaved $event)
    {
        $mailbox = $event->getMailbox();
        $processSettings = $mailbox->getProcessSettings();
        if (!$processSettings instanceof CaseMailboxProcessSettings) {
            return;
        }
        $organization = $mailbox->getOrganization();
        $this->tagManager->setTags($processSettings, $processSettings->getTags());
        $this->tagManager->saveTagging($processSettings, true, $organization);
    }
}
