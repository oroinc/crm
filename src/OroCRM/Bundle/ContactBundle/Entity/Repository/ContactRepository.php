<?php

namespace OroCRM\Bundle\ContactBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

use Oro\Bundle\EmailBundle\Entity\Repository\EmailAwareRepository;

class ContactRepository extends EntityRepository implements EmailAwareRepository
{
    /**
     * @param string $fullNameQueryPart
     * @param array $excludedEmails
     * @param string|null $query
     *
     * @return QueryBuilder
     */
    public function getPrimaryEmailsQb($fullNameQueryPart, array $excludedEmails = [], $query = null)
    {
        $qb = $this->createQueryBuilder('c');

        $qb
            ->select(sprintf('%s AS name', $fullNameQueryPart))
            ->addSelect('c.id AS entityId, c.email')
            ->orderBy('name')
            ->andWhere('c.email IS NOT NULL');

        if ($query) {
            $qb
                ->andWhere($qb->expr()->orX(
                    $qb->expr()->like($fullNameQueryPart, ':query'),
                    $qb->expr()->like('c.email', ':query')
                ))
                ->setParameter('query', sprintf('%%%s%%', $query));
        }

        if ($excludedEmails) {
            $qb
                ->andWhere($qb->expr()->notIn('c.email', ':excluded_emails'))
                ->setParameter('excluded_emails', $excludedEmails);
        }

        return $qb;
    }

    /**
     * @param string $fullNameQueryPart
     * @param array $excludedEmails
     * @param string|null $query
     *
     * @return QueryBuilder
     */
    public function getSecondaryEmailsQb($fullNameQueryPart, array $excludedEmails = [], $query = null)
    {
        $qb = $this->createQueryBuilder('c');

        $qb->select(sprintf('%s AS name', $fullNameQueryPart))
            ->addSelect('c.id AS entityId, e.email')
            ->orderBy('name')
            ->join('c.emails', 'e');

        if ($query) {
            $qb
                ->andWhere($qb->expr()->orX(
                    $qb->expr()->like($fullNameQueryPart, ':query'),
                    $qb->expr()->like('e.email', ':query')
                ))
                ->setParameter('query', sprintf('%%%s%%', $query));
        }

        if ($excludedEmails) {
            $qb
                ->andWhere($qb->expr()->notIn('e.email', ':excluded_emails'))
                ->setParameter('excluded_emails', $excludedEmails);
        }

        return $qb;
    }
}
