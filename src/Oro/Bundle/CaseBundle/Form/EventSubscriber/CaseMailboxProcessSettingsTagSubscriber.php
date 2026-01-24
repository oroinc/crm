<?php

namespace Oro\Bundle\CaseBundle\Form\EventSubscriber;

use Oro\Bundle\CaseBundle\Entity\CaseMailboxProcessSettings;
use Oro\Bundle\EmailBundle\Entity\Mailbox;
use Oro\Bundle\TagBundle\Entity\Tag;
use Oro\Bundle\TagBundle\Form\EventSubscriber\TagSubscriber;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * Subscribes to form events and dynamically configures case mailbox process settings tags.
 */
class CaseMailboxProcessSettingsTagSubscriber extends TagSubscriber
{
    #[\Override]
    public static function getSubscribedEvents(): array
    {
        return array_merge(
            parent::getSubscribedEvents(),
            [FormEvents::POST_SUBMIT => 'postSubmit']
        );
    }

    #[\Override]
    public function preSet(FormEvent $event)
    {
        $mailbox = $event->getForm()->getRoot()->getData();
        if ($mailbox instanceof Mailbox) {
            $organization = $mailbox->getOrganization();
            $this->organization = $organization;
        }

        parent::preSet($event);
    }

    public function postSubmit(FormEvent $event)
    {
        $entity = $event->getForm()->getParent()->getData();
        if (!$entity instanceof CaseMailboxProcessSettings || !$entity->getMailbox()) {
            return;
        }

        /** @var Tag[] $tags */
        $tags = $event->getForm()->getData();

        foreach ($tags as $tag) {
            $tag->setOrganization($entity->getMailbox()->getOrganization());
        }
    }
}
