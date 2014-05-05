<?php

namespace OroCRM\Bundle\MagentoBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use OroCRM\Bundle\ContactBundle\Entity\Contact;
use OroCRM\Bundle\MagentoBundle\Entity\Customer;

class CustomerRepository extends EntityRepository
{
    /**
     * @param Contact $contact
     *
     * @return Customer
     */
    public function getCustomerRelatedToContact(Contact $contact)
    {
        return $this->findOneBy(['contact' => $contact]);
    }
}
