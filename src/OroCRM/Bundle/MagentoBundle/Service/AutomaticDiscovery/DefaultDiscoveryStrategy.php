<?php

namespace OroCRM\Bundle\MagentoBundle\Service\AutomaticDiscovery;

use Doctrine\ORM\QueryBuilder;

use Symfony\Component\PropertyAccess\PropertyAccess;

class DefaultDiscoveryStrategy extends AbstractDiscoveryStrategy
{
    /**
     * {@inheritdoc}
     */
    public function apply(QueryBuilder $qb, $rootAlias, $field, array $configuration, $entity)
    {
        $propertyAccessor = PropertyAccess::createPropertyAccessor();
        $fieldValue = $propertyAccessor->getValue($entity, $field);

        $parameterName = ':' . $field;
        $qbFieldName = $rootAlias . '.' . $field;

        $qb->andWhere($this->getFieldExpr($qb, $qbFieldName, $parameterName, $configuration))
            ->setParameter($parameterName, $fieldValue);
    }
}
