<?php

namespace Oro\Bundle\CaseBundle\Form\EventSubscriber;

use Oro\Bundle\CaseBundle\Entity\CaseMailboxProcessSettings;
use Oro\Bundle\EmailBundle\Entity\Mailbox;
use Oro\Bundle\TagBundle\Entity\Tag;
use Oro\Bundle\TagBundle\Form\EventSubscriber\TagSubscriber;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class CaseMailboxProcessSettingsTagSubscriber extends TagSubscriber
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array_merge(
            parent::getSubscribedEvents(),
            [FormEvents::POST_SUBMIT => 'postSubmit']
        );
    }

    /**
     * {@inheritdoc}
     */
    public function preSet(FormEvent $event)
    {
        $mailbox = $event->getForm()->getRoot()->getData();
        if ($mailbox instanceof Mailbox) {
            $organization = $mailbox->getOrganization();
            $this->organization = $organization;
        }

        parent::preSet($event);
    }

    /**
     * {@inheritdoc}
     */
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
