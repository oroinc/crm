<?php

namespace OroCRM\Bundle\ContactBundle\EventListener;

use Doctrine\ORM\QueryBuilder;

use Oro\Bundle\DataGridBundle\Datasource\OrmDatasource;
use Oro\Bundle\DataGridBundle\Event\BuildAfter;
use Oro\Bundle\EmailBundle\Datagrid\EmailQueryFactory;
use Oro\Bundle\EmailBundle\Entity\Util\EmailUtil;

class ContactEmailGridListener
{
    /** @var  EmailQueryFactory */
    protected $queryFactory;

    public function __construct(EmailQueryFactory $factory)
    {
        $this->queryFactory = $factory;
    }

    /**
     * @param BuildAfter $event
     */
    public function onBuildAfter(BuildAfter $event)
    {
        $datasource = $event->getDatagrid()->getDatasource();
        if ($datasource instanceof OrmDatasource) {
            /** @var QueryBuilder $query */
            $queryBuilder = $datasource->getQuery();

            $this->queryFactory->prepareQuery($queryBuilder);

            // TODO: find contact
            $contact = 'something';
            $emailAddresses = EmailUtil::extractEmailAddresses($contact->getEmails());


            $this->addRecipientsQuery($queryBuilder, $emailAddresses);
        }
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param $emailAddresses
     */
    protected function addRecipientsQuery(QueryBuilder $queryBuilder, $emailAddresses)
    {
        $qbRecipients = $queryBuilder->getEntityManager()->createQueryBuilder()
            ->select('re.id')
            ->from('OroEmailBundle:Email', 're')
            ->innerJoin('re.recipients', 'r')
            ->innerJoin('r.emailAddress', 'ra')
            ->where('ra.email IN (:email_addresses)');

        $queryBuilder->setParameter('email_addresses', !empty($emailAddresses) ? $emailAddresses : null);

        $queryBuilder->orWhere(
            $queryBuilder->expr()->in('e.id', $qbRecipients->getDQL())
        );
    }
}
