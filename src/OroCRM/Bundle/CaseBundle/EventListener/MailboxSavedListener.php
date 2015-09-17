<?php

namespace OroCRM\Bundle\CaseBundle\EventListener;

use Oro\Bundle\EmailBundle\Event\MailboxSaved;
use Oro\Bundle\TagBundle\Entity\TagManager;

use OroCRM\Bundle\CaseBundle\Entity\CaseMailboxProcessSettings;

class MailboxSavedListener
{
    /** @var TagManager */
    private $tagManager;

    /**
     * @param TagManager $tagManager
     */
    public function __construct(TagManager $tagManager)
    {
        $this->tagManager = $tagManager;
    }

    /**
     * @param MailboxSaved $event
     */
    public function onMailboxSave(MailboxSaved $event)
    {
        $processSetings = $event->getMailbox()->getProcessSettings();
        if (!$processSetings instanceof CaseMailboxProcessSettings) {
            return;
        }

        $organization = $event->getMailbox()->getOrganization();
        $this->tagManager->saveTagging($processSetings, true, $organization);
    }
}
