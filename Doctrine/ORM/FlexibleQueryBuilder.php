<?php
namespace Oro\Bundle\FlexibleEntityBundle\Doctrine\ORM;

use Oro\Bundle\FlexibleEntityBundle\Exception\FlexibleConfigurationException;

use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\FlexibleEntityBundle\Entity\Attribute;
use Oro\Bundle\FlexibleEntityBundle\Model\AbstractAttributeType;
use Oro\Bundle\FlexibleEntityBundle\Exception\FlexibleQueryException;

/**
 * Extends query builder to add useful shortcuts which allow to easily select, filter or sort a flexible entity values
 *
 * It works exactly as classic QueryBuilder
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
 */
class FlexibleQueryBuilder extends QueryBuilder
{

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
     * Prepare join to attribute condition with current locale and scope criterias
     *
     * @param Attribute $attribute attribute
     * @param string $joinAlias the value join alias
     *
     * @return string
     */
    public function prepareAttributeJoinCondition(Attribute $attribute, $joinAlias)
    {
        $condition = $joinAlias.'.attribute = '.$attribute->getId();

        if ($attribute->getTranslatable()) {
            $condition .= ' AND '.$joinAlias.'.locale = '.$this->expr()->literal($this->getLocale());
        }
        if ($attribute->getScopable()) {
            $condition .= ' AND '.$joinAlias.'.scope = '.$this->expr()->literal($this->getScope());
        }

        return $condition;
    }

    /**
     * Add an attribute to select
     *
     * @param Attribute $attribute attribute
     *
     * @return QueryBuilder This QueryBuilder instance.
     *
    public function addAttributeToSelect(Attribute $attribute)
    {
        $joinAlias = 'select'.$attribute->getCode();
        $this->addSelect($joinAlias);
        $condition = $this->prepareAttributeJoinCondition($attribute, $joinAlias);
        $this->leftJoin($this->getRootAlias().'.'.$attribute->getBackendStorage(), $joinAlias, 'WITH', $condition);

        // TODO keep reference on this alias to order ?

        return $this;
    }*/

    /**
     * Get allowed operators for related backend type
     *
     * TODO : should be enrich for dates and options!
     * TODO : deal with null
     *
     * @param string $backendType
     *
     * @return multitype:string
     */
    public function getAllowedOperators($backendType)
    {
        $typeToOperator = array(
            AbstractAttributeType::BACKEND_TYPE_DATE     => array('eq'),
            AbstractAttributeType::BACKEND_TYPE_DATETIME => array('eq'),
            AbstractAttributeType::BACKEND_TYPE_DECIMAL  => array('eq', 'neq', 'lt', 'lte', 'gt', 'gte'),
            AbstractAttributeType::BACKEND_TYPE_INTEGER  => array('eq', 'neq', 'lt', 'lte', 'gt', 'gte'),
            AbstractAttributeType::BACKEND_TYPE_OPTION   => array('in', 'notIn'),
            AbstractAttributeType::BACKEND_TYPE_TEXT     => array('eq', 'neq', 'like'),
            AbstractAttributeType::BACKEND_TYPE_VARCHAR  => array('eq', 'neq', 'like'),
        );

        if (!isset($typeToOperator[$backendType])) {
            throw new FlexibleQueryException('backend type '.$backendType.' is unknown');
        }

        return $typeToOperator[$backendType];
    }

    /**
     * Prepare join to attribute condition with operator and value criteria
     *
     * @param Attribute    $attribute      the attribute
     * @param string       $backendField   the backend field name
     * @param string       $operator       the operator used to filter
     * @param string|array $attributeValue the value(s) to filter
     *
     * @return string
     */
    public function prepareAttributeCriteriaCondition(Attribute $attribute, $backendField, $operator, $attributeValue)
    {
        switch ($operator)
        {
            case 'eq':
                $condition = $this->expr()->eq($backendField, $this->expr()->literal($attributeValue));
                break;
            case 'neq':
                $condition = $this->expr()->neq($backendField, $this->expr()->literal($attributeValue));
                break;
            case 'like':
                $condition = $this->expr()->like($backendField, $this->expr()->literal($attributeValue));
                break;
            case 'lt':
                $condition = $this->expr()->lt($backendField, $this->expr()->literal($attributeValue));
                break;
            case 'lte':
                $condition = $this->expr()->lte($backendField, $this->expr()->literal($attributeValue));
                break;
            case 'gt':
                $condition = $this->expr()->gt($backendField, $this->expr()->literal($attributeValue));
                break;
            case 'gte':
                $condition = $this->expr()->gte($backendField, $this->expr()->literal($attributeValue));
                break;
            case 'isNull':
                $condition = $this->expr()->isNull($backendField);
                break;
            case 'isNotNull':
                $condition = $this->expr()->isNotNull($backendField);
                break;
            case 'in':
                $condition = $this->expr()->in($backendField, $attributeValue);
                break;
            case 'notIn':
                $condition = $this->expr()->notIn($backendField, $attributeValue);
                break;
            default:
                throw new FlexibleQueryException('operator '.$operator.' is unknown');
        }

        return $condition;
    }

    /**
     * Add an attribute to filter
     *
     * @param Attribute $attribute attribute
     *
     * @return QueryBuilder This QueryBuilder instance.
     */
    public function addAttributeFilter(Attribute $attribute, $operator, $attributeValue)
    {
        $allowed = $this->getAllowedOperators($attribute->getBackendType());
        if (!in_array($operator, $allowed)) {
            throw new FlexibleQueryException($operator.' is not allowed for type '.$attribute->getBackendType());
        }

        // prepare condition with locale and scope
        $joinAlias = 'filter'.$attribute->getCode();
        $joinValue = 'filterValue'.$attribute->getCode();

        // prepare condition with operator and value
        if ($attribute->getBackendType() == AbstractAttributeType::BACKEND_TYPE_OPTION) {

            // TODO : deal with locale and scope

            // join to value and option with filter on option id
            $this->innerJoin($this->getRootAlias().'.' . $attribute->getBackendStorage(), $joinAlias);
            $joinAliasOpt = 'filterO'.$attribute->getCode();
            $backendField = sprintf('%s.%s', $joinAliasOpt, 'id');
            $condition = $this->prepareAttributeCriteriaCondition($attribute, $backendField, $operator, $attributeValue);
            $this->innerJoin($joinAlias.'.options', $joinAliasOpt, 'WITH', $condition);

        } else {

            // apply condition on value backend
            $backendField = sprintf('%s.%s', $joinAlias, $attribute->getBackendType());
            $condition = $this->prepareAttributeJoinCondition($attribute, $joinAlias);
            $condition .= ' AND '.$this->prepareAttributeCriteriaCondition($attribute, $backendField, $operator, $attributeValue);
            $this->innerJoin($this->getRootAlias().'.'.$attribute->getBackendStorage(), $joinAlias, 'WITH', $condition);

        }

        return $this;
    }

    /**
     * Sort by attribute value
     *
     * @param Attribute $attribute the attribute to sort on
     * @param string    $direction direction to use
     */
     public function addAttributeOrderBy(Attribute $attribute, $direction)
     {
         if ($attribute->getBackendType() == AbstractAttributeType::BACKEND_TYPE_OPTION) {

             $aliasPrefix = 'sorter';

             // join to value
             $joinAliasVal    = $aliasPrefix.'V'.$attributeCode;
             $joinAliasOpt    = $aliasPrefix.'O'.$attributeCode;
             $joinAliasOptVal = $aliasPrefix.'OV'.$attributeCode;

             // TODO : deal with locale and scope !!!!!!

             $this->innerJoin($this->getRootAlias().'.' . $attribute->getBackendStorage(), $joinAliasVal);
             $this->innerJoin($joinAliasVal.'.options', $joinAliasOpt, 'WITH', $joinAliasOpt.".attribute = ".$attribute->getId());
             $this->innerJoin($joinAliasOpt.'.optionValues', $joinAliasOptVal, 'WITH', $joinAliasOptVal.".locale = 'en_US'"); // TODO !!!

             $this->addOrderBy($joinAliasOptVal.'.value', $direction);

         } else {

             $joinAlias = 'sorterV'.$attribute->getCode();
             $condition = $this->prepareAttributeJoinCondition($attribute, $joinAlias);
             $this->leftJoin($this->getRootAlias().'.'.$attribute->getBackendStorage(), $joinAlias, 'WITH', $condition);
             $this->addOrderBy($joinAlias.'.'.$attribute->getBackendType(), $direction);
         }
     }
}