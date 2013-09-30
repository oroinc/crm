<?php

namespace OroCRM\Bundle\ContactBundle\Datagrid;

use Oro\Bundle\EmailBundle\Datagrid\EmailQueryFactory;
use Oro\Bundle\GridBundle\Datagrid\ORM\EntityProxyQuery;
use Oro\Bundle\EmailBundle\Entity\Repository\EmailRepository;

class ContactEmailQueryFactory extends EmailQueryFactory
{
    /**
     * {@inheritDoc}
     */
    public function createQuery()
    {
        $entityManager = $this->registry->getManagerForClass($this->className);
        /** @var EmailRepository $repository */
        $repository = $entityManager->getRepository($this->className);
        $this->queryBuilder = $repository->createEmailListForAddressesQueryBuilder();
        $this->prepareQuery($this->queryBuilder);

        return new EntityProxyQuery($this->queryBuilder);
    }
}
