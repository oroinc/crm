<?php

namespace Oro\Bundle\ContactBundle\Entity\Repository;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\ContactBundle\Entity\ContactPhone;

/**
 * Doctrine repository for managing database operations with contact phone numbers
 */
class ContactPhoneRepository extends EntityRepository
{
    /**
     * @param Contact $contact
     * @return QueryBuilder
     */
    public function getContactPhoneQueryBuilder(Contact $contact)
    {
        return $this->createQueryBuilder('p')
            ->where('p.owner = :contact')
            ->orderBy('p.primary', 'DESC')
            ->setParameter('contact', $contact->getId(), Types::INTEGER);
    }

    /**
     * @param Contact $contact
     * @return ContactPhone[]
     */
    public function getContactPhones(Contact $contact)
    {
        $query = $this->getContactPhoneQueryBuilder($contact)->getQuery();

        return $query->execute();
    }
}
