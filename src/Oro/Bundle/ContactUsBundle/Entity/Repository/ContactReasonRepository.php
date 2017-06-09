<?php

namespace Oro\Bundle\ContactUsBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

use Oro\Bundle\ContactUsBundle\Entity\ContactReason;

class ContactReasonRepository extends EntityRepository
{
    /**
     * @param int $id
     *
     * @return ContactReason | null
     */
    public function getContactReason($id)
    {
        return $this->createQueryBuilder('cr')
            ->where('cr.id = :id')
            ->andWhere('cr.deletedAt IS NULL')
            ->setParameter('id', (int)$id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Gets existed contact reasons
     *
     * @return QueryBuilder
     */
    public function getExistedContactReasonsQB()
    {
        $qb = $this->createQueryBuilder('cr')
            ->where('cr.deletedAt IS NULL')
            ->orderBy('cr.id', 'ASC');

        return $qb;
    }
}
