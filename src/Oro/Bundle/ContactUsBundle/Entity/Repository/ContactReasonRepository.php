<?php

namespace Oro\Bundle\ContactUsBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;

use Oro\Bundle\ContactUsBundle\Entity\ContactReason;

class ContactReasonRepository extends EntityRepository
{
    /**
     * @param ContactReason $contactReason
     *
     * @return bool
     */
    public function isContactReasonExists(ContactReason $contactReason)
    {
        $result = $this->createQueryBuilder('cr')
            ->where('cr.id = :id')
            ->andWhere('cr.deletedAt IS NULL')
            ->setParameter('id', $contactReason)
            ->getQuery()
            ->getOneOrNullResult();

        return $result != null ? true : false;
    }
}
