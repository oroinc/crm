<?php

namespace OroCRM\Bundle\ContactBundle\EventListener;

use Oro\Bundle\SearchBundle\Event\PrepareResultItemEvent;

use OroCRM\Bundle\ContactBundle\Entity\Contact;

class PrepareResultItemListener
{
    /**
     * @param PrepareResultItemEvent $event
     */
    public function prepareEmailItemDataEvent(PrepareResultItemEvent $event)
    {
        if ($event->getResultItem()->getEntityName() !== 'OroCRM\Bundle\ContactBundle\Entity\Contact') {
            return;
        }

        $contact = $event->getResultItem()->getEntity();
        $event->getResultItem()->setRecordTitle($this->getContactTitle($contact));
    }

    /**
     * @param Contact $contact
     *
     * @return string
     */
    protected function getContactTitle(Contact $contact)
    {
        return $contact->getFirstName() || $contact->getLastName()
            ? null
            : (string) ($contact->getPrimaryPhone() ?: $contact->getPrimaryEmail());
    }
}
