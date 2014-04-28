<?php

namespace OroCRM\Bundle\ContactBundle\EventListener;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;

use Oro\Bundle\DataGridBundle\Event\BuildAfter;
use Oro\Bundle\DataGridBundle\Datagrid\RequestParameters;
use Oro\Bundle\DataGridBundle\Datasource\Orm\OrmDatasource;

use Oro\Bundle\EmailBundle\Entity\Util\EmailUtil;

class ContactEmailGridListener
{
    /** @var RequestParameters */
    protected $requestParams;

    /** @var  EntityManager */
    protected $em;

    public function __construct(RequestParameters $requestParams, EntityManager $em)
    {
        $this->requestParams = $requestParams;
        $this->em            = $em;
    }

    /**
     * @param BuildAfter $event
     */
    public function onBuildAfter(BuildAfter $event)
    {
        $datasource = $event->getDatagrid()->getDatasource();
        if ($datasource instanceof OrmDatasource) {
            /** @var QueryBuilder $query */
            $queryBuilder = $datasource->getQueryBuilder();

            if ($id = $this->requestParams->get('contactId')) {
                $contact = $this->em
                    ->getRepository('OroCRMContactBundle:Contact')
                    ->find($id);

                $emails = $contact->getEmails();
            } else {
                $emails = [];
            }

            $emailAddresses = EmailUtil::extractEmailAddresses($emails);

            $this->addRecipientsQuery($queryBuilder, $emailAddresses);
        }
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param              $emailAddresses
     */
    protected function addRecipientsQuery(QueryBuilder $queryBuilder, $emailAddresses)
    {
        $qbRecipients = $this->em->createQueryBuilder()
            ->select('re.id')
            ->from('OroEmailBundle:Email', 're')
            ->innerJoin('re.recipients', 'r')
            ->innerJoin('r.emailAddress', 'ra')
            ->where('ra.email IN (:email_addresses)');

        $qbDuplicates = $this->em->createQueryBuilder()
            ->select('email')
            ->from('OroEmailBundle:Email', 'email')
            ->groupBy('email.messageId')
            ->having('MAX(email.id) = e.id');

        $queryBuilder->where(
            $queryBuilder->expr()->andX(
                $queryBuilder->expr()->exists(
                    $qbDuplicates->getDQL()
                ),
                $queryBuilder->expr()->orX(
                    $queryBuilder->expr()->in('a.email', ':email_addresses'),
                    $queryBuilder->expr()->in('e.id', $qbRecipients->getDQL())
                )
            )
        );
        
        $queryBuilder->setParameter('email_addresses', !empty($emailAddresses) ? $emailAddresses : null);
    }
}
