<?php

namespace Oro\Bundle\ContactBundle\Entity\Provider;

use Doctrine\DBAL\Platforms\PostgreSqlPlatform;
use Doctrine\ORM\EntityManagerInterface;
use Oro\Bundle\BatchBundle\ORM\Query\BufferedQueryResultIterator;
use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\ContactBundle\Entity\ContactEmail;
use Oro\Bundle\EmailBundle\Entity\EmailOwnerInterface;
use Oro\Bundle\EmailBundle\Entity\Provider\EmailOwnerProviderInterface;

/**
 * The email address owner provider for Contact entity.
 */
class EmailOwnerProvider implements EmailOwnerProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function getEmailOwnerClass(): string
    {
        return Contact::class;
    }

    /**
     * {@inheritdoc}
     */
    public function findEmailOwner(EntityManagerInterface $em, string $email): ?EmailOwnerInterface
    {
        $qb = $em->createQueryBuilder()
            ->from(ContactEmail::class, 'ce')
            ->select('ce')
            ->join('ce.owner', 'c')
            ->setParameter('email', $email)
            ->setMaxResults(1);
        if ($em->getConnection()->getDatabasePlatform() instanceof PostgreSqlPlatform) {
            $qb->where('LOWER(ce.email) = LOWER(:email)');
        } else {
            $qb->where('ce.email = :email');
        }

        /** @var ContactEmail|null $emailEntity */
        $emailEntity = $qb->getQuery()->getOneOrNullResult();
        if (null === $emailEntity) {
            return null;
        }

        return $emailEntity->getOwner();
    }

    /**
     * {@inheritdoc}
     */
    public function getOrganizations(EntityManagerInterface $em, string $email): array
    {
        $qb = $em->createQueryBuilder()
            ->from(ContactEmail::class, 'ce')
            ->select('IDENTITY(c.organization) AS id')
            ->join('ce.owner', 'c')
            ->setParameter('email', $email);
        if ($em->getConnection()->getDatabasePlatform() instanceof PostgreSqlPlatform) {
            $qb->where('LOWER(ce.email) = LOWER(:email)');
        } else {
            $qb->where('ce.email = :email');
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
    public function getEmails(EntityManagerInterface $em, int $organizationId): iterable
    {
        $qb = $em->createQueryBuilder()
            ->from(ContactEmail::class, 'ce')
            ->select('ce.email')
            ->join('ce.owner', 'c')
            ->where('c.organization = :organizationId')
            ->setParameter('organizationId', $organizationId)
            ->orderBy('ce.id');
        $iterator = new BufferedQueryResultIterator($qb);
        $iterator->setBufferSize(5000);
        foreach ($iterator as $row) {
            yield $row['email'];
        }
    }
}
