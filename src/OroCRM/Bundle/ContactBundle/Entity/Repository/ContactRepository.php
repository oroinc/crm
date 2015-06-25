<?php

namespace OroCRM\Bundle\ContactBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;

use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;

class ContactRepository extends EntityRepository
{
    /**
     * @param AclHelper $aclHelper
     * @param array $excludedEmails
     * @param string|null $query
     * @param int $limit
     *
     * @return array
     */
    public function getEmails(AclHelper $aclHelper, array $excludedEmails = [], $query = null, $limit = 100)
    {
        $primaryEmails = $this->getPrimaryEmails($aclHelper, $excludedEmails, $query, $limit);

        $excludedEmails = array_merge($excludedEmails, $primaryEmails);
        $limit -= count($primaryEmails);

        $secondaryEmails = $this->getSecondaryEmails($aclHelper, $excludedEmails, $query, $limit);

        $emailResults = array_merge($primaryEmails, $secondaryEmails);

        $emails = [];
        foreach ($emailResults as $row) {
            $emails[$row['email']] = sprintf('%s <%s>', $row['name'], $row['email']);
        }

        return $emails;
    }

    /**
     * @param AclHelper $aclHelper
     * @param array $excludedEmails
     * @param string|null $query
     * @param int $limit
     *
     * @return array
     */
    protected function getPrimaryEmails(AclHelper $aclHelper, array $excludedEmails = [], $query = null, $limit = 100)
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

        return $aclHelper->apply($qb)->getResult();
    }

    /**
     * @param AclHelper $aclHelper
     * @param array $excludedEmails
     * @param string|null $query
     * @param int $limit
     *
     * @return array
     */
    protected function getSecondaryEmails(
        AclHelper $aclHelper,
        array $excludedEmails = [],
        $query = null,
        $limit = 100
    ) {
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

        return $aclHelper->apply($qb)->getResult();
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
