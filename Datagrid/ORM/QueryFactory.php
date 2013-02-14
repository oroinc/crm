<?php

namespace Oro\Bundle\GridBundle\Datagrid\ORM;

use Oro\Bundle\GridBundle\Datagrid\QueryFactoryInterface;
use Oro\Bundle\GridBundle\Datagrid\ProxyQueryInterface;
use Symfony\Bridge\Doctrine\RegistryInterface;

class QueryFactory implements QueryFactoryInterface
{
    /**
     * @var RegistryInterface
     */
    protected $registry;

    /**
     * @var string
     */
    protected $className;

    /**
     * @var string
     */
    protected $alias;

    /**
     * @param RegistryInterface $registry
     * @param string $className
     * @param string $alias
     */
    public function __construct(RegistryInterface $registry, $className, $alias = 'o')
    {
        $this->registry  = $registry;
        $this->className = $className;
        $this->alias     = $alias;
    }

    /**
     * @return ProxyQuery
     */
    public function createQuery()
    {
        $entityManager = $this->registry->getEntityManagerForClass($this->className);
        $queryBuilder  = $entityManager->getRepository($this->className)->createQueryBuilder($this->alias);

        return new ProxyQuery($queryBuilder);
    }
}
