<?php

namespace OroCRM\Bundle\ContactBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;

class ContactRepository extends EntityRepository
{
    /**
     * @param array $excludedEmails
     * @param string|null $query
     * @param int $limit
     *
     * @return array
     */
    public function getEmails(array $excludedEmails = [], $query = null, $limit = 100)
    {
        $primaryEmails = $this->getPrimaryEmails($excludedEmails, $query, $limit);
        $secondaryEmails = $this->getSecondaryEmails($excludedEmails, $query, $limit - count($primaryEmails));

        $emailResults = array_merge($primaryEmails, $secondaryEmails);

        $emails = [];
        foreach ($emailResults as $row) {
            $emails[$row['email']] = sprintf('%s <%s>', $row['name'], $row['email']);
        }

        return $emails;
    }

    /**
     * @param array $excludedEmails
     * @param string|null $query
     * @param int $limit
     *
     * @return array
     */
    protected function getPrimaryEmails(array $excludedEmails = [], $query = null, $limit = 100)
    {
        $qb = $this->createQueryBuilder('c');

        $fullName = $this->getFullNameQueryPart();

        $qb
            ->select(sprintf('%s AS name', $fullName))
            ->addSelect('c.email')
            ->andWhere('c.email IS NOT NULL')
            ->setMaxResults($limit);

        if ($query) {
            $qb
                ->andWhere($qb->expr()->orX(
                    $qb->expr()->like($fullName, ':query'),
                    $qb->expr()->like('c.email', ':query')
                ))
                ->setParameter('query', sprintf('%%%s%%', $query));
        }

        if ($excludedEmails) {
            $qb
                ->andWhere($qb->expr()->notIn('c.email', ':excluded_emails'))
                ->setParameter('excluded_emails', $excludedEmails);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * @param array $excludedEmails
     * @param string|null $query
     * @param int $limit
     *
     * @return array
     */
    protected function getSecondaryEmails(array $excludedEmails = [], $query = null, $limit = 100)
    {
        $qb = $this->createQueryBuilder('c');

        $fullName = $this->getFullNameQueryPart();

        $qb->select(sprintf('%s AS name', $fullName))
            ->addSelect('e.email')
            ->join('c.emails', 'e')
            ->setMaxResults($limit);

        if ($query) {
            $qb
                ->andWhere($qb->expr()->orX(
                    $qb->expr()->like($fullName, ':query'),
                    $qb->expr()->like('e.email', ':query')
                ))
                ->setParameter('query', sprintf('%%%s%%', $query));
        }

        if ($excludedEmails) {
            $qb
                ->andWhere($qb->expr()->notIn('e.email', ':excluded_emails'))
                ->setParameter('excluded_emails', $excludedEmails);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * @return string
     */
    protected function getFullNameQueryPart()
    {
        $fields = [
            'namePrefix',
            'nameSuffix',
            'firstName',
            'middleName',
            'lastName',
        ];

        $alias = 'c';
        $queryParts = array_map(function ($part) use ($alias) {
            return sprintf('COALESCE(%s.%s, \' \')', $alias, $part);
        }, $fields);

        return sprintf('CONCAT(%s)', implode(', ', $queryParts));
    }
}
