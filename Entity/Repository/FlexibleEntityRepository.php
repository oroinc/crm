<?php
namespace Oro\Bundle\FlexibleEntityBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\FlexibleEntityBundle\Doctrine\ORM\FlexibleQueryBuilder;
use Oro\Bundle\FlexibleEntityBundle\Exception\UnknownAttributeException;
use Oro\Bundle\FlexibleEntityBundle\Model\Behavior\TranslatableInterface;
use Oro\Bundle\FlexibleEntityBundle\Model\Behavior\ScopableInterface;
use Oro\Bundle\FlexibleEntityBundle\Entity\Attribute;
use Oro\Bundle\FlexibleEntityBundle\Model\AbstractAttributeType;

/**
 * Base repository for flexible entity
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
 */
class FlexibleEntityRepository extends EntityRepository implements TranslatableInterface, ScopableInterface
{
    /**
     * Flexible entity config
     * @var array
     */
    protected $flexibleConfig;

    /**
     * Locale code
     * @var string
     */
    protected $locale;

    /**
     * Scope code
     * @var string
     */
    protected $scope;

    /**
     * Entity alias
     * @var string
     */
    protected $entityAlias;

    /**
     * Get flexible entity config
     *
     * @return array $config
     */
    public function getFlexibleConfig()
    {
        return $this->flexibleConfig;
    }

    /**
     * Set flexible entity config

     * @param array $config
     *
     * @return FlexibleEntityRepository
     */
    public function setFlexibleConfig($config)
    {
        $this->flexibleConfig = $config;

        return $this;
    }

    /**
     * Get locale code
     *
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * Set locale code
     *
     * @param string $code
     *
     * @return FlexibleEntityRepository
     */
    public function setLocale($code)
    {
        $this->locale = $code;

        return $this;
    }

    /**
     * Get scope code
     *
     * @return string
     */
    public function getScope()
    {
        return $this->scope;
    }

    /**
     * Set scope code
     *
     * @param string $code
     *
     * @return FlexibleEntityRepository
     */
    public function setScope($code)
    {
        $this->scope = $code;

        return $this;
    }

    /**
     * Finds attributes
     *
     * @param array $attributeCodes attribute codes
     *
     * @throws UnknownAttributeException
     *
     * @return array The objects.
     */
    public function getCodeToAttributes(array $attributeCodes)
    {
        // prepare entity attributes query
        $attributeAlias = 'Attribute';
        $attributeName = $this->flexibleConfig['attribute_class'];
        $attributeRepo = $this->_em->getRepository($attributeName);
        $qb = $attributeRepo->createQueryBuilder($attributeAlias);
        $qb->andWhere('Attribute.entityType = :type')->setParameter('type', $this->_entityName);

        // filter by code
        if (!empty($attributeCodes)) {
            $qb->andWhere($qb->expr()->in('Attribute.code', $attributeCodes));
        }

        // prepare associative array
        $attributes = $qb->getQuery()->getResult();
        $codeToAttribute = array();
        foreach ($attributes as $attribute) {
            $codeToAttribute[$attribute->getCode()]= $attribute;
        }

        // raise exception
        if (!empty($attributeCodes) and count($attributeCodes) != count($codeToAttribute)) {
            $missings = array_diff($attributeCodes, array_keys($codeToAttribute));
            throw new UnknownAttributeException(
                'Attribute(s) with code '.implode(', ', $missings).' not exists for entity '.$this->_entityName
            );
        }

        return $codeToAttribute;
    }

    /**
     * Find flexible attribute by code
     *
     * @param string $code
     *
     * @throws UnknownAttributeException
     *
     * @return AbstractEntityAttribute
     */
    public function findAttributeByCode($code)
    {
        $attributeName = $this->flexibleConfig['attribute_class'];
        $attributeRepo = $this->_em->getRepository($attributeName);
        $attribute = $attributeRepo->findOneBy(array('entityType' => $this->_entityName, 'code' => $code));

        return $attribute;
    }

    /**
     * Create a new QueryBuilder instance that is prepopulated for this entity name
     *
     * @param string  $alias          alias for entity
     * @param boolean $attributeCodes add selects on values only for this attribute codes list
     *
     * @return QueryBuilder $qb
     *
    public function createQueryBuilder($alias, $attributeCodes = null)
    {
        $this->entityAlias = $alias;
        $qb = new FlexibleQueryBuilder($this->_em);
        $qb->setLocale($this->getLocale());
        $qb->setScope($this->getScope());

        if (empty($attributeCodes)) {
            $qb->select($alias)->from($this->_entityName, $alias);

        } else {
            $qb->select($alias, 'Value', 'Attribute', 'ValueOption')
                ->from($this->_entityName, $this->entityAlias)
                ->leftJoin($this->entityAlias.'.values', 'Value')
                ->leftJoin('Value.attribute', 'Attribute')
                ->leftJoin('Value.options', 'ValueOption')
                ->leftJoin('ValueOption.optionValues', 'AttributeOptionValue')
                ->where($qb->expr()->in('Attribute.code', $attributeCodes));

            // TODO : we should filter select by current locale and scope to reduce value number
        }

        return $qb;
    }*/

    /**
     * Create a new QueryBuilder instance that allow to automatically join on attribute values and allow doctrine
     * hydratation as real flexible entity, value, option and attributes
     *
     * @param string  $alias          alias for entity
     * @param boolean $attributeCodes add selects on values only for this attribute codes list
     *
     * @return QueryBuilder $qb
     */
    public function createFlexibleQueryBuilder($alias, $attributeCodes = null)
    {
        $this->entityAlias = $alias;
        $qb = new FlexibleQueryBuilder($this->_em);
        $qb->setLocale($this->getLocale());
        $qb->setScope($this->getScope());

        $qb->select($alias, 'Value', 'Attribute', 'ValueOption')
            ->from($this->_entityName, $this->entityAlias)
            ->leftJoin($this->entityAlias.'.values', 'Value')
            ->leftJoin('Value.attribute', 'Attribute')
            ->leftJoin('Value.options', 'ValueOption')
            ->leftJoin('ValueOption.optionValues', 'AttributeOptionValue');

        // TODO : we should filter select by current locale and scope to reduce values number

        if (!empty($attributeCodes)) {
            $qb->where($qb->expr()->in('Attribute.code', $attributeCodes));
        }

        return $qb;
    }

    /**
     * Prepare a new QueryBuilder instance with select, criterias and order by
     *
     * @param array      $attributes attribute codes
     * @param array      $criteria   criterias
     * @param array|null $orderBy    order by
     *
     * @return QueryBuilder $qb
     *
    public function prepareQueryBuilder(array $attributes = null, array $criteria = null, array $orderBy = null)
    {
        // identify kind of query
        $hasSelectedAttributes = (!is_null($attributes) and !empty($attributes));
        $hasCriterias = (!is_null($criteria) and !empty($criteria));
        if ($hasCriterias or $hasSelectedAttributes) {
            $codeToAttribute = $this->getCodeToAttributes($attributes);
        }
        // get base query builder (direct join to attribute and value if no attribute selection)
        if (!$hasSelectedAttributes) {
            $qb = $this->createQueryBuilder('Entity');
        } else {
            $qb = $this->createQueryBuilder('Entity', array_keys($codeToAttribute)); // lazy load
        }
        // add criterias
        $attributeCodeToAlias = array();
        if ($criteria and !empty($criteria)) {
            $attributeCodeToAlias = $this->addFieldOrAttributeCriterias(
                $qb,
                $attributes,
                $criteria,
                $codeToAttribute,
                $attributeCodeToAlias
            );
        }
        // get selected attributes values (but not used as criteria)
        if (!empty($attributes)) {
            $attributeCodeToAlias = $this->addAttributeToSelect(
                $qb,
                $attributes,
                $codeToAttribute,
                $attributeCodeToAlias,
                $orderBy
            );
        }
        // add order by
        if ($orderBy) {
            $this->addFieldOrAttributeOrderBy($qb, $orderBy, $attributeCodeToAlias);
        }

        return $qb;
    }*/


    /**
     * Finds entities and attributes values by a set of criteria.
     *
     * @param array      $attributes attribute codes
     * @param array      $criteria   criterias
     * @param array|null $orderBy    order by
     * @param int|null   $limit      limit
     * @param int|null   $offset     offset
     *
     * @return array The objects.
     */
    public function findByWithAttributes(array $attributes = null, array $criteria = null, array $orderBy = null, $limit = null, $offset = null)
    {
        $qb = $this->createFlexibleQueryBuilder('Entity', $attributes);
        $codeToAttribute = $this->getCodeToAttributes($attributes);
        $attributes = array_keys($codeToAttribute);

        // TODO : deal with more operators ?

        // add criterias
        foreach ($criteria as $attCode => $attValue) {
            if (in_array($attCode, $attributes)) {
                $attribute = $codeToAttribute[$attCode];
                $qb->addAttributeFilter($attribute, 'eq', $attValue);
            } else {
                $qb->andWhere($qb->expr()->eq($this->entityAlias.'.'.$attCode, $qb->expr()->literal($attValue)));
            }
        }

        // add sorts
        foreach ($orderBy as $attCode => $direction) {
            if (in_array($attCode, $attributes)) {
                $attribute = $codeToAttribute[$attCode];
                $qb->addAttributeOrderBy($attribute, $direction);
            } else {
                $qb->addOrderBy($this->entityAlias.'.'.$attCode, $direction);
            }
        }

        // use doctrine paginator to avoid count problem with left join of values
        if (!is_null($offset) and !is_null($limit)) {
            $qb->setFirstResult($offset)->setMaxResults($limit);
            $paginator = new Paginator($qb->getQuery(), $fetchJoinCollection = true);

            return $paginator;
        }

        return $qb->getQuery()->getResult();
    }


    /**
     * Finds entities and attributes values by a set of criteria.
     *
     * @param array      $attributes attribute codes
     * @param array      $criteria   criterias
     * @param array|null $orderBy    order by
     * @param int|null   $limit      limit
     * @param int|null   $offset     offset
     *
     * @return array The objects.
     *
    public function findByWithAttributes(array $attributes = null, array $criteria = null, array $orderBy = null, $limit = null, $offset = null)
    {
        // prepare query builder
        $qb = $this->prepareQueryBuilder($attributes, $criteria, $orderBy);

        // add limit
        if (!is_null($offset) and !is_null($limit)) {
            $qb->setFirstResult($offset)->setMaxResults($limit);
            // use doctrine paginator to avoid count problem with left join of values
            $paginator = new Paginator($qb->getQuery(), $fetchJoinCollection = true);

            return $paginator;
        }

        return $qb->getQuery()->getResult();
    }*/

    /**
     * Add attributes to select
     *
     * @param QueryBuilder $qb                   query builder to update
     * @param array        $attributes           attributes to select
     * @param array        $codeToAttribute      attribute code to attribute
     * @param array        $attributeCodeToAlias attribute code to query alias
     * @param array        $orderBy              attribute to order by, aims to determine if we get a localized value
     *
     * @return array $attributeCodeToAlias
     *
    protected function addAttributeToSelect($qb, $attributes, $codeToAttribute, $attributeCodeToAlias, $orderBy = array())
    {
        foreach ($attributes as $attributeCode) {
            // add select attribute value
            $joinAlias = 'selectV'.$attributeCode;
            $qb->addSelect($joinAlias);
            // prepare join condition
            $attribute = $codeToAttribute[$attributeCode];
            $joinValue = 'selectv'.$attributeCode;
            $condition = $joinAlias.'.attribute = '.$attribute->getId();
            // add condition to get only translated value if we use this attribute to order
            if ($attribute->getTranslatable() and isset($orderBy[$attributeCode])) {
                $joinValueLocale = 'selectL'.$attributeCode;
                $condition .= ' AND '.$joinAlias.'.locale = :'.$joinValueLocale;
                $qb->setParameter($joinValueLocale, $this->getLocale());
            }
            // add condition to get only scoped value if we use this attribute to order
            if ($attribute->getScopable() and isset($orderBy[$attributeCode])) {
                $joinValueScope = 'selectS'.$attributeCode;
                $condition .= ' AND '.$joinAlias.'.scope = :'.$joinValueScope;
                $qb->setParameter($joinValueScope, $this->getScope());
            }
            // add the join with condition and store alias for next uses
            $qb->leftJoin($this->entityAlias.'.'.$attribute->getBackendStorage(), $joinAlias, 'WITH', $condition);
            $attributeCodeToAlias[$attributeCode]= $joinAlias.'.'.$attribute->getBackendType();
        }

        return $attributeCodeToAlias;
    }*/

    /**
     * Add fields and/or attributes criterias
     *
     * @param QueryBuilder $qb                   query builder to update
     * @param array        $attributes           attributes to select
     * @param array        $criteria             criterias on field or attribute
     * @param array        $codeToAttribute      attribute code to attribute
     * @param array        $attributeCodeToAlias attribute code to query alias
     *
     * @return array $attributeCodeToAlias
     *
    protected function addFieldOrAttributeCriterias(
        $qb,
        $attributes,
        $criteria,
        $codeToAttribute,
        $attributeCodeToAlias
    ) {
        foreach ($criteria as $fieldCode => $fieldValue) {
            // add attribute criteria
            if (in_array($fieldCode, $attributes)) {
                $attribute = $codeToAttribute[$fieldCode];
                $this->addAttributeCriteria($qb, $attribute, $fieldCode, $fieldValue);
            } else {
                // add field criteria
                $qb->andWhere($this->entityAlias.'.'.$fieldCode.' = :'.$fieldCode)->setParameter($fieldCode, $fieldValue);
            }
        }

        return $attributeCodeToAlias;
    }*/

    /**
     * Add attribute criteria
     *
     * @param QueryBuilder $qb         query builder to update
     * @param Attribute    $attribute  attribute
     * @param string       $fieldCode  criterias on field or attribute
     * @param string       $fieldValue filter value
     * @param string       $operator   operator to use
     *
    protected function addAttributeCriteria(QueryBuilder $qb, $attribute, $fieldCode, $fieldValue, $operator = '=')
    {
        $aliasPrefix = 'filter';
        $joinAlias = 'filterV'.$fieldCode;
        $condition = $this->prepareJoinAttributeCondition($qb, $attribute, $fieldCode, $aliasPrefix);

        // prepare condition
        $joinValue = 'filterv'.$fieldCode;
        $condition .= ' AND '.$joinAlias.'.'.$attribute->getBackendType().' '.$operator.' :'.$joinValue;

        // add inner join to filter lines and store value alias for next uses
        $qb->innerJoin($this->entityAlias . '.' . $attribute->getBackendStorage(), $joinAlias, 'WITH', $condition)
            ->setParameter($joinValue, $fieldValue);
    }*/

    /**
     * Prepare join condition
     *
     * @param QueryBuilder $qb          query builder to update
     * @param Attribute    $attribute   attribute
     * @param string       $fieldCode   criterias on field or attribute
     * @param array        $aliasPrefix alias prefix
     *
     * @return string
     *
    protected function prepareJoinAttributeCondition(QueryBuilder $qb, $attribute, $fieldCode, $aliasPrefix)
    {
        $joinAlias = $aliasPrefix . 'V' . $fieldCode;
        $condition = $joinAlias . '.attribute = ' . $attribute->getId();

        // add condition on locale if attribute is translatable
        if ($attribute->getTranslatable()) {
            $joinValueLocale = $aliasPrefix. 'L' . $fieldCode;
            $condition .= ' AND '.$joinAlias.'.locale = :'.$joinValueLocale;
            $qb->setParameter($joinValueLocale, $this->getLocale());
        }

        // add condition on scope if attribute is scopable
        if ($attribute->getScopable()) {
            $joinValueScope = $aliasPrefix . 'S' . $fieldCode;
            $condition .= ' AND '.$joinAlias.'.scope = :'.$joinValueScope;
            $qb->setParameter($joinValueScope, $this->getScope());
        }

        return $condition;
    }*/

    /**
     * Apply a filter by attribute
     *
     * @param QueryBuilder $qb             query builder to update
     * @param string       $attributeCode  attribute code
     * @param string|array $attributeValue value(s) used to filter
     * @param string       $operator       operator to use
     *
    public function applyFilterByAttribute(QueryBuilder $qb, $attributeCode, $attributeValue, $operator = '=')
    {
        // TODO ensure allowed operator

        $attributes = $this->getCodeToAttributes(array($attributeCode));
        if ($attributes) {
             @var $attribute Attribute
            $attribute = $attributes[$attributeCode];

            if ($attribute->getBackendType() != AbstractAttributeType::BACKEND_TYPE_OPTION) {
                $this->addAttributeCriteria(
                    $qb,
                    $attribute,
                    $attribute->getCode(),
                    $attributeValue,
                    $operator
                );
            } else {
                // join to value
                $joinAliasVal = 'filterV'.$attributeCode;
                $qb->innerJoin($this->entityAlias.'.' . $attribute->getBackendStorage(), $joinAliasVal);

                // join to option (custom backend)
                $joinAliasOpt = 'filterO'.$attributeCode;
                $conditionOpt = $qb->expr()->in(sprintf('%s.%s', $joinAliasOpt, 'id'), $attributeValue);
                $qb->innerJoin($joinAliasVal.'.options', $joinAliasOpt, 'WITH', $conditionOpt);
            }
        }
    }*/

    /**
     * Sort by attribute value
     *
     * @param QueryBuilder $qb            query builder to update
     * @param string       $attributeCode attribute code
     * @param string       $direction     direction to use
     *
    public function applySorterByAttribute(QueryBuilder $qb, $attributeCode, $direction)
    {


        $attributes = $this->getCodeToAttributes(array($attributeCode));
        $attributeCodeToAlias = array();

        if ($attributes) {
            /* @var $attribute Attribute
            $attribute = $attributes[$attributeCode];

            if ($attribute->getBackendType() != AbstractAttributeType::BACKEND_TYPE_OPTION) {
                $aliasPrefix = 'sorter';
                $joinAlias = $aliasPrefix . 'V' . $attribute->getCode();
                $condition = $this->prepareJoinAttributeCondition($qb, $attribute, $attribute->getCode(), $aliasPrefix);

                // add left join to filter lines and store value alias for next uses
                $qb->leftJoin($this->entityAlias . '.' . $attribute->getBackendStorage(), $joinAlias, 'WITH', $condition);
                $attributeCodeToAlias[$attribute->getCode()] = $joinAlias.'.'.$attribute->getBackendType();

                $orderBy = array($attribute->getCode() => $direction);
                $this->addFieldOrAttributeOrderBy($qb, $orderBy, $attributeCodeToAlias);

            } else {

                $aliasPrefix = 'sorter';

                // join to value
                $joinAliasVal    = $aliasPrefix.'V'.$attributeCode;
                $joinAliasOpt    = $aliasPrefix.'O'.$attributeCode;
                $joinAliasOptVal = $aliasPrefix.'OV'.$attributeCode;

                // TODO : deal with locale and scope

                $qb->innerJoin($this->entityAlias.'.' . $attribute->getBackendStorage(), $joinAliasVal);
                $qb->innerJoin($joinAliasVal.'.options', $joinAliasOpt, 'WITH', $joinAliasOpt.".attribute = ".$attribute->getId());
                $qb->innerJoin($joinAliasOpt.'.optionValues', $joinAliasOptVal, 'WITH', $joinAliasOptVal.".locale = 'en_US'");

                $qb->addOrderBy($joinAliasOptVal.'.value', $direction);
            }
        }
    }*/

    /**
     * Add fields and/or attributes order by
     *
     * @param QueryBuilder $qb                   query builder to update
     * @param array        $orderBy              fields and attributes order by
     * @param array        $attributeCodeToAlias attribute code to query alias
     *
    protected function addFieldOrAttributeOrderBy($qb, $orderBy, $attributeCodeToAlias)
    {
        foreach ($orderBy as $fieldCode => $direction) {
            // on attribute value
            if (isset($attributeCodeToAlias[$fieldCode])) {
                $qb->addOrderBy($attributeCodeToAlias[$fieldCode], $direction);
                // on entity field
            } else {
                $qb->addOrderBy($this->entityAlias.'.'.$fieldCode, $direction);
            }
        }
    }*/

    /**
     * Find entity with attributes values
     *
     * @param int $id entity id
     *
     * @return Entity the entity
     */
    public function findWithAttributes($id)
    {
        $products = $this->findByWithAttributes(array(), array('id' => $id));

        return current($products);
    }
}
