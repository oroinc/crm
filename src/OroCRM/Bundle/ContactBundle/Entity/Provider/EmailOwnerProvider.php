<?php

namespace OroCRM\Bundle\ContactBundle\Entity\Provider;

use Doctrine\ORM\EntityManager;
use OroCRM\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\EmailBundle\Entity\Provider\EmailOwnerProviderInterface;

class EmailOwnerProvider implements EmailOwnerProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function getEmailOwnerClass()
    {
        return 'OroCRM\Bundle\ContactBundle\Entity\Contact';
    }

    /**
     * {@inheritdoc}
     */
    public function findEmailOwner(EntityManager $em, $email)
    {
        // TODO: Need to be refactored after contact emails database structure is arranged

        /** @var Contact $contact */
        $contact = null;

        /** @var Contact $c */
        foreach ($em->getRepository('OroCRMContactBundle:Contact')->findAll() as $c) {
            foreach ($c->getEmails() as $contactEmail) {
                if (strcasecmp($contactEmail, $email)) {
                    $contact = $c;
                    break;
                }
            }
            if ($contact !== null) {
                break;
            }
        }

        return $contact;
    }
}
