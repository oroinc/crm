<?php

namespace Oro\Bundle\GridBundle\Datagrid\ORM;

use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery as BaseProxyQuery;
use Oro\Bundle\GridBundle\Datagrid\ProxyQueryInterface;

class ProxyQuery extends BaseProxyQuery implements ProxyQueryInterface
{
    /**
     * @var \Doctrine\ORM\QueryBuilder
     */
    protected $queryBuilder;

    /**
     * {@inheritdoc}
     */
    public function execute(array $params = array(), $hydrationMode = null)
    {
        return $this->queryBuilder->getQuery()->execute($params, $hydrationMode);
    }

    /**
     * {@inheritdoc}
     */
    public function entityJoin(array $associationMappings)
    {
        $aliases = $this->queryBuilder->getRootAliases();
        $alias = array_shift($aliases);

        $newAlias = 's';

        foreach ($associationMappings as $associationMapping) {
            $newAlias .= '_' . $associationMapping['fieldName'];
            if (!in_array($newAlias, $this->entityJoinAliases)) {
                $this->entityJoinAliases[] = $newAlias;
                $this->queryBuilder->leftJoin(sprintf('%s.%s', $alias, $associationMapping['fieldName']), $newAlias);
            }

            $alias = $newAlias;
        }

        return $alias;
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getQueryBuilder()
    {
        return $this->getQueryBuilder();
    }
}
