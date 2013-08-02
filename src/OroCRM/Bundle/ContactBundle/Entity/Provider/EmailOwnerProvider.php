<?php

namespace OroCRM\Bundle\ContactBundle\Entity\Provider;

use Doctrine\ORM\EntityManager;
use OroCRM\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\EmailBundle\Entity\Provider\EmailOwnerProviderInterface;
use Oro\Bundle\FlexibleEntityBundle\Entity\Repository\FlexibleEntityRepository;

class EmailOwnerProvider implements EmailOwnerProviderInterface
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * Constructor
     *
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * {@inheritdoc}
     */
    public function findEmailOwner($emailAddress)
    {
        // TODO: Need to be refactored after contact emails database structure is arranged

        /** @var Contact $contact */
        $contact = null;

        /** @var Contact $c */
        foreach ($this->em->getRepository('OroCRM\Bundle\ContactBundle\Entity\Contact')->findAll() as $c) {
            foreach ($c->getEmails() as $email) {
                if (strcasecmp($email, $emailAddress)) {
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
