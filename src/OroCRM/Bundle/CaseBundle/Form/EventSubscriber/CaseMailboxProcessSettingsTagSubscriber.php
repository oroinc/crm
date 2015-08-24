<?php

namespace OroCRM\Bundle\CaseBundle\Form\EventSubscriber;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

use Oro\Bundle\TagBundle\Form\EventSubscriber\TagSubscriber;

use OroCRM\Bundle\CaseBundle\Entity\CaseMailboxProcessSettings;

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
        $entity = $event->getForm()->getParent()->getData();
        if ($entity instanceof CaseMailboxProcessSettings && $entity->getMailbox()) {
            $organization = $entity->getMailbox()->getOrganization();
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

        $tags = array_merge(
            $event->getForm()->get('all')->getData(),
            $event->getForm()->get('owner')->getData()
        );

        foreach ($tags as $tag) {
            $tag->setOrganization($entity->getMailbox()->getOrganization());
        }
    }
}
