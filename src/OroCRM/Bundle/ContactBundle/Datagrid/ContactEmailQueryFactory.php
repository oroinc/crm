<?php

namespace OroCRM\Bundle\ContactBundle\Datagrid;

use Oro\Bundle\GridBundle\Datagrid\ORM\QueryFactory\EntityQueryFactory;
use Oro\Bundle\GridBundle\Datagrid\ORM\EntityProxyQuery;
use Oro\Bundle\EmailBundle\Entity\Repository\EmailRepository;

class ContactEmailQueryFactory extends EntityQueryFactory
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

        return new EntityProxyQuery($this->queryBuilder);
    }
}
