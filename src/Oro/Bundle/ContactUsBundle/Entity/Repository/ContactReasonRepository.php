<?php
declare(strict_types=1);

namespace Oro\Bundle\ContactUsBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\ContactUsBundle\Entity\ContactReason;

/**
 * Entity repository for Oro\Bundle\ContactUsBundle\Entity\ContactReason
 * @see \Oro\Bundle\ContactUsBundle\Entity\ContactReason
 */
class ContactReasonRepository extends EntityRepository
{
    /**
     * Retrieves a contact reason by id. Ignores deleted contact reasons.
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getContactReason(int $id): ?ContactReason
    {
        return $this->createQueryBuilder('cr')
            ->where('cr.id = :id')
            ->andWhere('cr.deletedAt IS NULL')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Query builder that selects only with existing (non-deleted) contact reasons.
     */
    public function createExistingContactReasonsQB(): QueryBuilder
    {
        return $this->createQueryBuilder('cr')
            ->where('cr.deletedAt IS NULL')
            ->orderBy('cr.id', 'ASC');
    }

    /**
     * Query builder that selects existing (non-deleted) contacts reasons and their localized (all) titles.
     */
    public function createExistingContactReasonsWithTitlesQB(): QueryBuilder
    {
        return $this->createExistingContactReasonsQB()
            ->addSelect('titles')
            ->leftJoin('cr.titles', 'titles');
    }

    /**
     * @return ContactReason[]
     */
    public function findAllExistingWithTitles(): array
    {
        return $this->createExistingContactReasonsWithTitlesQB()->getQuery()->getResult();
    }
}
