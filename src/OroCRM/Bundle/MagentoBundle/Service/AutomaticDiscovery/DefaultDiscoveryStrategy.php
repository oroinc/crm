<?php

namespace OroCRM\Bundle\MagentoBundle\Service\AutomaticDiscovery;

use Doctrine\ORM\QueryBuilder;

use Symfony\Component\PropertyAccess\PropertyAccess;

use OroCRM\Bundle\MagentoBundle\DependencyInjection\Configuration;

class DefaultDiscoveryStrategy implements DiscoveryStrategyInterface
{
    /**
     * {@inheritdoc}
     */
    public function apply(QueryBuilder $qb, $rootAlias, $field, $configuration, $entity)
    {
        $propertyAccessor = PropertyAccess::createPropertyAccessor();

        $parameterName = ':' . $field;
        $qbFieldName = $rootAlias . '.' . $field;
        $fieldExpr = $qb->expr()->eq($qbFieldName, $parameterName);

        $options = $configuration[Configuration::DISCOVERY_OPTIONS_KEY];
        if (!empty($options[Configuration::DISCOVERY_EMPTY_KEY])) {
            $fieldExpr = $qb->expr()->orX(
                $fieldExpr,
                $qb->expr()->eq($qbFieldName, ':emptyValue'),
                $qb->expr()->isNull($qbFieldName)
            );
            $qb->setParameter('emptyValue', '');
        }

        $fieldValue = $propertyAccessor->getValue($entity, $field);
        $qb->andWhere($fieldExpr)
            ->setParameter($parameterName, $fieldValue);
    }
}
