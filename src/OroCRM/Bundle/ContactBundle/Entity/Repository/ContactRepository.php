<?php

namespace OroCRM\Bundle\ContactBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

use Oro\Bundle\EmailBundle\Entity\Repository\EmailAwareRepository;

class ContactRepository extends EntityRepository implements EmailAwareRepository
{
    /**
     * {@inheritdoc}
     */
    public function getPrimaryEmailsQb($fullNameQueryPart, array $excludedEmailNames = [], $query = null)
    {
        $qb = $this->createQueryBuilder('c');

        $qb
            ->select(sprintf('%s AS name', $fullNameQueryPart))
            ->addSelect('c.id AS entityId, c.email, o.name AS organization')
            ->orderBy('name')
            ->leftJoin('c.organization', 'o')
            ->andWhere('c.email IS NOT NULL');

        if ($query) {
            $qb
                ->andWhere($qb->expr()->orX(
                    $qb->expr()->like($fullNameQueryPart, ':query'),
                    $qb->expr()->like('c.email', ':query')
                ))
                ->setParameter('query', sprintf('%%%s%%', $query));
        }

        if ($excludedEmailNames) {
            $qb
                ->andWhere($qb->expr()->notIn(
                    sprintf('TRIM(CONCAT(%s, \' <\', c.email, \'>|\', o.name))', $fullNameQueryPart),
                    ':excluded_emails'
                ))
                ->setParameter('excluded_emails', array_values($excludedEmailNames));
        }

        return $qb;
    }

    /**
     * {@inheritdoc}
     */
    public function getSecondaryEmailsQb($fullNameQueryPart, array $excludedEmailNames = [], $query = null)
    {
        $qb = $this->createQueryBuilder('c');

        $qb->select(sprintf('%s AS name', $fullNameQueryPart))
            ->addSelect('c.id AS entityId, e.email, o.name AS organization')
            ->orderBy('name')
            ->join('c.emails', 'e')
            ->leftJoin('c.organization', 'o');

        if ($query) {
            $qb
                ->andWhere($qb->expr()->orX(
                    $qb->expr()->like($fullNameQueryPart, ':query'),
                    $qb->expr()->like('e.email', ':query')
                ))
                ->setParameter('query', sprintf('%%%s%%', $query));
        }

        if ($excludedEmailNames) {
            $qb
                ->andWhere($qb->expr()->notIn(
                    sprintf('TRIM(CONCAT(%s, \' <\', e.email, \'>|\', o.name))', $fullNameQueryPart),
                    ':excluded_emails'
                ))
                ->setParameter('excluded_emails', array_values($excludedEmailNames));
        }

        return $qb;
    }
}
