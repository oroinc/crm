<?php

namespace Oro\Bundle\GridBundle\Filter\ORM;

use Symfony\Component\Translation\TranslatorInterface;
use Doctrine\DBAL\Query\QueryBuilder;
use Sonata\DoctrineORMAdminBundle\Filter\Filter as AbstractORMFilter;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Oro\Bundle\GridBundle\Field\FieldDescriptionInterface;

abstract class AbstractFilter extends AbstractORMFilter
{
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
     * Specify if current filter must use HAVING instead of WHERE clause
     *
     * @return bool
     */
    protected function isComplexField()
    {
        return $this->getOption('complex_data') == true;
    }

    /**
     * @param ProxyQueryInterface $queryBuilder
     * @param array $value
     * @return array
     */
    protected function association(ProxyQueryInterface $queryBuilder, $value)
    {
        $alias = $this->getOption('entity_alias')
            ?: $queryBuilder->entityJoin($this->getParentAssociationMappings());

        return array($alias, $this->getFieldName());
    }

    /**
     * {@inheritdoc}
     */
    public function getFieldType()
    {
        return $this->getOption('type', FieldDescriptionInterface::TYPE_TEXT);
    }
}
