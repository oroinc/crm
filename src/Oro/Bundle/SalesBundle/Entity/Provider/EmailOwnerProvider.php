<?php

namespace Oro\Bundle\SalesBundle\Entity\Provider;

use Doctrine\DBAL\Platforms\PostgreSqlPlatform;
use Doctrine\ORM\EntityManager;
use Oro\Bundle\BatchBundle\ORM\Query\BufferedQueryResultIterator;
use Oro\Bundle\EmailBundle\Entity\Provider\EmailOwnerProviderInterface;
use Oro\Bundle\SalesBundle\Entity\Lead;
use Oro\Bundle\SalesBundle\Entity\LeadEmail;

/**
 * The email address owner provider for Lead entity.
 */
class EmailOwnerProvider implements EmailOwnerProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function getEmailOwnerClass()
    {
        return Lead::class;
    }

    /**
     * {@inheritdoc}
     */
    public function findEmailOwner(EntityManager $em, $email)
    {
        $qb = $em->createQueryBuilder()
            ->from(LeadEmail::class, 'le')
            ->select('le')
            ->join('le.owner', 'l')
            ->setParameter('email', $email);
        if ($em->getConnection()->getDatabasePlatform() instanceof PostgreSqlPlatform) {
            $qb->where('LOWER(le.email) = LOWER(:email)');
        } else {
            $qb->where('le.email = :email');
        }
        /** @var LeadEmail|null $emailEntity */
        $emailEntity = $qb->getQuery()->getOneOrNullResult();
        if (null === $emailEntity) {
            return null;
        }

        return $emailEntity->getOwner();
    }

    /**
     * {@inheritdoc}
     */
    public function getOrganizations(EntityManager $em, $email)
    {
        $qb = $em->createQueryBuilder()
            ->from(LeadEmail::class, 'le')
            ->select('IDENTITY(l.organization) AS id')
            ->join('le.owner', 'l')
            ->setParameter('email', $email);
        if ($em->getConnection()->getDatabasePlatform() instanceof PostgreSqlPlatform) {
            $qb->where('LOWER(le.email) = LOWER(:email)');
        } else {
            $qb->where('le.email = :email');
        }
        $rows = $qb->getQuery()->getArrayResult();

        $result = [];
        foreach ($rows as $row) {
            $result[] = (int)$row['id'];
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getEmails(EntityManager $em, $organizationId)
    {
        $qb = $em->createQueryBuilder()
            ->from(LeadEmail::class, 'le')
            ->select('le.email')
            ->join('le.owner', 'l')
            ->where('l.organization = :organizationId')
            ->setParameter('organizationId', $organizationId)
            ->orderBy('le.id');
        $iterator = new BufferedQueryResultIterator($qb);
        $iterator->setBufferSize(5000);
        foreach ($iterator as $row) {
            yield $row['email'];
        }
    }
}
