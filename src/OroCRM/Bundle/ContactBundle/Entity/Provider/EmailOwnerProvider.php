<?php

namespace OroCRM\Bundle\ContactBundle\Entity\Provider;

use Doctrine\ORM\EntityManager;
use OroCRM\Bundle\ContactBundle\Entity\Contact;
use OroCRM\Bundle\ContactBundle\Entity\ContactEmail;
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
        /** @var Contact $contact */
        $contact = null;

        /** @var ContactEmail $emailEntity */
        $emailEntity = $em->getRepository('OroCRMContactBundle:ContactEmail')
            ->findOneBy(array('email' => $email));
        if ($emailEntity !== null) {
            $contact = $emailEntity->getOwner();
        }

        return $contact;
    }
}
