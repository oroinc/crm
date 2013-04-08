<?php

namespace Oro\Bundle\GridBundle\Filter\ORM;

use Symfony\Component\Translation\TranslatorInterface;

use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\ORM\Query\Expr;

use Sonata\DoctrineORMAdminBundle\Filter\Filter as AbstractORMFilter;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;

use Oro\Bundle\GridBundle\Field\FieldDescriptionInterface;
use Oro\Bundle\GridBundle\Filter\FilterInterface;

abstract class AbstractFilter extends AbstractORMFilter implements FilterInterface
{
    /**
     * @var Expr
     */
    private $expressionFactory;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public function apply($queryBuilder, $value)
    {
        list($alias, $field) = $this->association($queryBuilder, $value);
        $this->value = $value;
        $this->filter($queryBuilder, $alias, $field, $value);
    }

    /**
     * {@inheritdoc}
     */
    protected function association(ProxyQueryInterface $queryBuilder, $value)
    {
        $alias = $queryBuilder->entityJoin($this->getParentAssociationMappings());

        $fieldMapping = $this->getFieldMapping();
        if (!empty($fieldMapping['entityAlias'])) {
            $alias = $fieldMapping['entityAlias'];
        }
        return array($alias, $this->getFieldName());
    }

    /**
     * Apply filter expression to having or where clause depending on configuration
     *
     * @param ProxyQueryInterface $queryBuilder
     * @param mixed $expression
     */
    protected function applyFilterToClause(ProxyQueryInterface $queryBuilder, $expression)
    {
        if ($this->isApplyFilterToHavingClause()) {
            $this->applyHaving(
                $queryBuilder,
                $expression
            );
        } else {
            $this->applyWhere(
                $queryBuilder,
                $expression
            );
        }
    }

    /**
     * Checks if filter expression should be applied to having clause, if not where clause should be applied
     *
     * @return bool
     */
    protected function isApplyFilterToHavingClause()
    {
        $fieldMapping = $this->getFieldMapping();
        return !empty($fieldMapping['fieldExpression']);
    }

    /**
     * Get field expression based on field name and alias. If field mapping has specific expression it will be
     * used instead as is.
     *
     * @param string $fieldName
     * @param string $alias
     * @return string
     */
    protected function createFieldExpression($fieldName, $alias)
    {
        $fieldMapping = $this->getFieldMapping();
        if (!empty($fieldMapping['fieldExpression'])) {
            return $fieldMapping['fieldExpression'];
        } else {
            return sprintf('%s.%s', $alias, $fieldName);
        }
    }

    /**
     * Create filter expression that will be applied
     *
     * @param mixed $leftExpression
     * @param string $operator
     * @param mixed $rightExpression
     * @return Expr\Comparison
     */
    protected function createComparisonExpression($leftExpression, $operator, $rightExpression)
    {
        return new Expr\Comparison($leftExpression, $operator, $rightExpression);
    }


    /**
     * Create comparison expression for field
     *
     * @param string $field
     * @param string $alias
     * @param string $operator
     * @param string $parameterName
     * @return Expr\Comparison
     */
    protected function createCompareFieldExpression($field, $alias, $operator, $parameterName)
    {
        return $this->createComparisonExpression(
            $this->createFieldExpression($field, $alias),
            $operator,
            ':' . $parameterName
        );
    }

    /**
     * Get expression factory
     *
     * @return Expr
     */
    protected function getExpressionFactory()
    {
        if (!$this->expressionFactory) {
            $this->expressionFactory = new Expr();
        }
        return $this->expressionFactory;
    }

    /**
     * Apply expression to having clause
     *
     * @param ProxyQueryInterface $queryBuilder
     * @param mixed $parameter
     */
    protected function applyHaving(ProxyQueryInterface $queryBuilder, $parameter)
    {
        /** @var $queryBuilder QueryBuilder */
        if ($this->getCondition() == self::CONDITION_OR) {
            $queryBuilder->orHaving($parameter);
        } else {
            $queryBuilder->andHaving($parameter);
        }

        // filter is active since it's added to the queryBuilder
        $this->active = true;
    }

    /**
     * {@inheritdoc}
     */
    public function getFieldType()
    {
        return $this->getOption('type', FieldDescriptionInterface::TYPE_TEXT);
    }

    /**
     * Filter type options
     *
     * @return array
     */
    public function getTypeOptions()
    {
        return array();
    }
}
