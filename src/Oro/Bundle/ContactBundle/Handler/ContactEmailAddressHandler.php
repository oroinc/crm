<?php

namespace Oro\Bundle\ContactBundle\Handler;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\ContactBundle\Entity\ContactEmail;
use Oro\Bundle\EmailBundle\Entity\Email;
use Oro\Bundle\EmailBundle\Entity\EmailRecipient;
use Oro\Bundle\EmailBundle\Entity\Manager\EmailAddressManager;
use Oro\Bundle\EntityBundle\ORM\InsertFromSelectQueryExecutor;
use Oro\Component\DoctrineUtils\ORM\QueryBuilderUtil;

/**
 * ContactEmailAddressHandler is responsible for actualization of EmailAddress records for ContactEmails.
 * First it removes records from EmailAddress for Contact emails that were removed for contact
 * Then it adds new Contact emails that are not present in EmailAddress
 */
class ContactEmailAddressHandler
{
    /**
     * @var InsertFromSelectQueryExecutor
     */
    private $insertFromSelectQueryExecutor;

    /**
     * @var EmailAddressManager
     */
    private $emailAddressManager;

    /**
     * @var ManagerRegistry
     */
    private $registry;

    public function __construct(
        InsertFromSelectQueryExecutor $insertFromSelectQueryExecutor,
        EmailAddressManager $emailAddressManager,
        ManagerRegistry $registry
    ) {
        $this->insertFromSelectQueryExecutor = $insertFromSelectQueryExecutor;
        $this->emailAddressManager = $emailAddressManager;
        $this->registry = $registry;
    }

    /**
     * @return string
     */
    private function getEmailAddressClass()
    {
        return $this->emailAddressManager->getEmailAddressProxyClass();
    }

    /**
     * Actualize EmailAddress records based on ContactEmail entities
     */
    public function actualizeContactEmailAssociations()
    {
        $this->deleteRemovedEmailAssociations();
        $this->addContactEmailAssociations();
    }

    /**
     * Add EmailAddress records based on ContactEmail entities
     */
    private function addContactEmailAssociations()
    {
        $contactAssociationFieldName = $this->getContactAssociationFieldName();
        if ($contactAssociationFieldName) {
            $qb = $this->getEntityManager()->createQueryBuilder();
            $nowString = $this->getCurrentTimestampString();

            $qb->from(ContactEmail::class, 'ce')
                ->select(
                    'ce.email',
                    'MAX(IDENTITY(ce.owner))',
                    (string)$qb->expr()->literal($nowString),
                    (string)$qb->expr()->literal($nowString),
                    'CASE WHEN ce.email IS NOT NULL THEN TRUE ELSE FALSE END'
                )
                ->leftJoin($this->getEmailAddressClass(), 'ea', Join::WITH, $qb->expr()->eq('ce.email', 'ea.email'))
                ->where($qb->expr()->isNull('ea.id'))
                ->groupBy('ce.email');

            $this->insertFromSelectQueryExecutor->execute(
                $this->getEmailAddressClass(),
                ['email', $contactAssociationFieldName, 'created', 'updated', 'hasOwner'],
                $qb
            );
        }
    }

    /**
     * Delete EmailAddress records which contains emails that are not present in ContactEmail
     */
    private function deleteRemovedEmailAssociations()
    {
        $idsToRemove = $this->getNonExistingEmailAssociationIds();
        if ($idsToRemove) {
            $deleteQb = $this->getEntityManager()
                ->createQueryBuilder()
                ->delete($this->getEmailAddressClass(), 'ea');
            $deleteQb->where($deleteQb->expr()->in('ea.id', ':deleteIds'))
                ->setParameter('deleteIds', $idsToRemove)
                ->getQuery()
                ->execute();
        }
    }

    private function getNonExistingEmailAssociationIds(): array
    {
        $contactAssociationFieldName = $this->getContactAssociationFieldName();
        if ($contactAssociationFieldName) {
            // Skip email addresses that was already synced
            $emailDQL = $this->getEntityManager()->createQueryBuilder()
                ->select('e.id')
                ->from(Email::class, 'e')
                ->where('e.fromEmailAddress = ea.id')
                ->getDQL();
            $emailRecipientDQL = $this->getEntityManager()->createQueryBuilder()
                ->select('er.id')
                ->from(EmailRecipient::class, 'er')
                ->where('er.emailAddress = ea.id')
                ->getDQL();

            QueryBuilderUtil::checkIdentifier($contactAssociationFieldName);
            $toDeleteQb = $this->getEntityManager()->createQueryBuilder();
            $toDeleteQb->select('ea.id')
                ->from($this->getEmailAddressClass(), 'ea')
                ->leftJoin(
                    ContactEmail::class,
                    'ce',
                    Join::WITH,
                    'ea.email = ce.email AND ce.owner = ea.' . $contactAssociationFieldName
                )
                ->where($toDeleteQb->expr()->isNull('ce.id'))
                ->andWhere($toDeleteQb->expr()->isNotNull('ea.' . $contactAssociationFieldName))
                ->andWhere(
                    $toDeleteQb->expr()->not(
                        $toDeleteQb->expr()->exists($emailDQL)
                    )
                )
                ->andWhere(
                    $toDeleteQb->expr()->not(
                        $toDeleteQb->expr()->exists($emailRecipientDQL)
                    )
                );

            $result = $toDeleteQb->getQuery()->getScalarResult();
            return array_map('current', $result);
        }

        return [];
    }

    /**
     * @return null|string
     */
    private function getContactAssociationFieldName()
    {
        $em = $this->getEntityManager();
        $metadata = $em->getClassMetadata($this->getEmailAddressClass());
        $contactAssociations = $metadata->getAssociationsByTargetClass(Contact::class);
        $contactAssociation = reset($contactAssociations);

        if ($contactAssociation && !empty($contactAssociation['fieldName'])) {
            return $contactAssociation['fieldName'];
        }

        return null;
    }

    /**
     * @return EntityManager
     */
    private function getEntityManager()
    {
        return $this->registry->getManagerForClass(ContactEmail::class);
    }

    private function getCurrentTimestampString(): string
    {
        $dateFormat = $this->getEntityManager()
            ->getConnection()
            ->getDatabasePlatform()
            ->getDateTimeFormatString();
        $now = new \DateTime('now', new \DateTimeZone('UTC'));

        return $now->format($dateFormat);
    }
}
