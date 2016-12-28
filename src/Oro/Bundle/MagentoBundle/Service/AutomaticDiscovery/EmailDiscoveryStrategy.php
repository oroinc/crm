<?php

namespace Oro\Bundle\MagentoBundle\Service\AutomaticDiscovery;

use Doctrine\ORM\QueryBuilder;

use Symfony\Component\PropertyAccess\PropertyAccess;

class EmailDiscoveryStrategy extends AbstractDiscoveryStrategy
{
    /**
     * {@inheritdoc}
     */
    public function apply(QueryBuilder $qb, $rootAlias, $field, array $configuration, $entity)
    {
        $propertyAccessor = PropertyAccess::createPropertyAccessor();
        $fieldValue = (string)$propertyAccessor->getValue($entity, $field);

        $parameterName = ':' . $field;
        $qbFieldName = $rootAlias . '.' . $field;

        $qb->andWhere($this->getFieldExpr($qb, $qbFieldName, $parameterName, $configuration))
            ->setParameter($parameterName, $fieldValue);
    }
}
