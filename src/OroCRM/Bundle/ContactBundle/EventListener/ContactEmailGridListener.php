<?php

namespace OroCRM\Bundle\ContactBundle\EventListener;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Oro\Bundle\DataGridBundle\Datasource\Orm\OrmDatasource;
use Oro\Bundle\DataGridBundle\Event\BuildAfter;
use Oro\Bundle\EmailBundle\Datagrid\EmailQueryFactory;
use Oro\Bundle\EmailBundle\Entity\Util\EmailUtil;

class ContactEmailGridListener
{
    /** @var  EmailQueryFactory */
    protected $queryFactory;

    /** @var \Symfony\Component\HttpFoundation\Request  */
    protected $request;

    /** @var  EntityManager */
    protected $em;

    public function __construct(ContainerInterface $container, EmailQueryFactory $factory)
    {
        $this->request = $container->get('request');
        $this->em      = $container->get('doctrine.orm.entity_manager');
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

            if ($id = $this->request->get('contactId')) {
                $contact = $this->em
                    ->getRepository('OroCRMContactBundle:Contact')
                    ->find($id);

                $emails = $contact->getEmails();
            } else {
                $emails = false;
            }

            $emailAddresses = EmailUtil::extractEmailAddresses($emails);

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
