<?php

namespace Oro\Bundle\MagentoBundle\Service\AutomaticDiscovery;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Query\Expr\Andx;
use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\AddressBundle\Entity\AddressType;
use Oro\Bundle\MagentoBundle\DependencyInjection\Configuration;
use Oro\Bundle\MagentoBundle\Exception\InvalidConfigurationException;
use Oro\Bundle\MagentoBundle\Exception\RuntimeException;
use Oro\Component\DoctrineUtils\ORM\QueryBuilderUtil;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;

class AddressDiscoveryStrategy extends AbstractDiscoveryStrategy
{
    const STRATEGY_ANY_OF = 'any_of';
    const STRATEGY_BY_TYPE = 'by_type';
    const STRATEGY_SHIPPING = 'shipping';
    const STRATEGY_BILLING = 'billing';

    /**
     * @var array
     */
    public static $strategies = [
        self::STRATEGY_ANY_OF,
        self::STRATEGY_BY_TYPE,
        self::STRATEGY_BILLING,
        self::STRATEGY_SHIPPING
    ];

    /**
     * @var PropertyAccessor
     */
    protected $propertyAccessor;

    public function __construct()
    {
        $this->propertyAccessor = PropertyAccess::createPropertyAccessor();
    }

    /**
     * {@inheritdoc}
     */
    public function apply(QueryBuilder $qb, $rootAlias, $field, array $configuration, $entity)
    {
        $addressAlias = QueryBuilderUtil::getField($rootAlias, $field);

        $fields = $configuration[Configuration::DISCOVERY_FIELDS_KEY][$field];
        if (!is_array($fields)) {
            throw new InvalidConfigurationException('Expected list of address fields');
        }
        $fields = array_keys($fields);

        $addresses = $this->propertyAccessor->getValue($entity, $field);

        if (!$addresses instanceof Collection) {
            throw new RuntimeException('Addresses expected to be an instance of Collection');
        }

        $strategy = $this->getMatchStrategy($field, $configuration);
        switch ($strategy) {
            case self::STRATEGY_ANY_OF:
                $this->matchAnyOf($qb, $addressAlias, $addresses, $fields, $configuration);
                break;
            case self::STRATEGY_SHIPPING:
                $this->matchByType($qb, $addressAlias, $addresses, $fields, $configuration, AddressType::TYPE_SHIPPING);
                break;
            case self::STRATEGY_BILLING:
                $this->matchByType($qb, $addressAlias, $addresses, $fields, $configuration, AddressType::TYPE_BILLING);
                break;
            case self::STRATEGY_BY_TYPE:
                $this->matchByTypeMatch($qb, $addressAlias, $addresses, $fields, $configuration);
                break;
            default:
                throw new InvalidConfigurationException(
                    sprintf('Expected one of "%s", "%s" given', implode(',', self::$strategies), $strategy)
                );
        }
    }

    /**
     * @param QueryBuilder $qb
     * @param string $addressAlias
     * @param Collection $addresses
     * @param array $fields
     * @param array $configuration
     */
    protected function matchAnyOf(
        QueryBuilder $qb,
        $addressAlias,
        Collection $addresses,
        array $fields,
        array $configuration
    ) {
        $alias = 'address01';
        $qb->leftJoin($addressAlias, $alias);

        $expr = $qb->expr()->orX();
        $idx = 0;
        foreach ($addresses as $address) {
            $expr->add(
                $this->getAddressMatchExpr($qb, $address, $fields, $alias, $idx, $configuration)
            );
            $idx++;
        }
        if ($expr->count()) {
            $qb->andWhere($expr);
        }
    }

    /**
     * @param QueryBuilder $qb
     * @param string $addressAlias
     * @param Collection $addresses
     * @param array $fields
     * @param array $configuration
     */
    protected function matchByTypeMatch(
        QueryBuilder $qb,
        $addressAlias,
        Collection $addresses,
        array $fields,
        array $configuration
    ) {
        $expr = $qb->expr()->orX();
        $idx = 0;
        foreach ($addresses as $address) {
            $addressTypes = $this->getAddressTypes($address);
            if ($addressTypes) {
                $alias = 'address'.$idx;
                $qb->leftJoin($addressAlias, $alias);
                $expr->add($this->getMatchByTypeExpr($qb, $alias, $address, $fields, $configuration, $addressTypes));
                $idx++;
            }
        }
        if ($expr->count()) {
            $qb->andWhere($expr);
        }
    }

    /**
     * @param QueryBuilder $qb
     * @param string $addressAlias
     * @param Collection $addresses
     * @param array $fields
     * @param array $configuration
     * @param string $type
     */
    protected function matchByType(
        QueryBuilder $qb,
        $addressAlias,
        Collection $addresses,
        array $fields,
        array $configuration,
        $type
    ) {
        $alias = 'address01';
        $qb->leftJoin($addressAlias, $alias);

        foreach ($addresses as $address) {
            $qb->andWhere($this->getMatchByTypeExpr($qb, $alias, $address, $fields, $configuration, [$type]));
        }
    }

    /**
     * @param QueryBuilder $qb
     * @param $alias
     * @param $address
     * @param array $fields
     * @param array $configuration
     * @param array $types
     *
     * @return Andx
     */
    protected function getMatchByTypeExpr(
        QueryBuilder $qb,
        $alias,
        $address,
        array $fields,
        array $configuration,
        array $types
    ) {
        $joinedTypes = implode('_', $types);
        $expr = $this->getAddressMatchExpr($qb, $address, $fields, $alias, $joinedTypes, $configuration);

        $typesAlias = $alias . 'Types';
        $qb->join($alias . '.types', $typesAlias);
        $qbFieldName = $typesAlias . '.name';
        $qbParameterName = ':type' . $joinedTypes;

        $expr->add($qb->expr()->in($qbFieldName, $qbParameterName));
        $qb->setParameter($qbParameterName, $types);

        return $expr;
    }

    /**
     * @param QueryBuilder $qb
     * @param object $addressEntity
     * @param array $fields
     * @param string $alias
     * @param int $idx
     * @param array $configuration
     * @return Andx
     */
    protected function getAddressMatchExpr(
        QueryBuilder $qb,
        $addressEntity,
        array $fields,
        $alias,
        $idx,
        array $configuration
    ) {
        $andExpr = $qb->expr()->andX();
        QueryBuilderUtil::checkIdentifier($alias);
        foreach ($fields as $field) {
            QueryBuilderUtil::checkIdentifier($field);
            $qbFieldName = $alias . '.' . $field;
            $qbParameterName = ':' . $field . (int)$idx;

            $andExpr->add($this->getFieldExpr($qb, $qbFieldName, $qbParameterName, $configuration));
            $fieldValue = $this->propertyAccessor->getValue($addressEntity, $field);
            $qb->setParameter($qbParameterName, $fieldValue);
        }

        return $andExpr;
    }

    /**
     * @param string $field
     * @param array $configuration
     * @return string
     */
    protected function getMatchStrategy($field, array $configuration)
    {
        $strategy = self::STRATEGY_ANY_OF;
        if (!empty($configuration[Configuration::DISCOVERY_STRATEGY_KEY][$field])) {
            $strategy = $configuration[Configuration::DISCOVERY_STRATEGY_KEY][$field];
        }

        if (!in_array($strategy, self::$strategies, true)) {
            $strategy = self::STRATEGY_ANY_OF;
        }

        return $strategy;
    }

    /**
     * @param object $address
     *
     * @return array
     */
    protected function getAddressTypes($address)
    {
        $addressTypes = $this->propertyAccessor->getValue($address, 'types');
        if ($addressTypes) {
            return array_map(
                function ($type) {
                    if ($type instanceof AddressType) {
                        return $type->getName();
                    }

                    return null;
                },
                $addressTypes->toArray()
            );
        }

        return [];
    }
}
