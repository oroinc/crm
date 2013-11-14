<?php

namespace OroCRM\Bundle\ContactBundle\Entity\Repository;

use Doctrine\ORM\Query;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

use OroCRM\Bundle\ContactBundle\Entity\Contact;
use OroCRM\Bundle\ContactBundle\Entity\ContactPhone;

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
            ->setParameter('contact', $contact);
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
