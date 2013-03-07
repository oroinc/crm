<?php

namespace Oro\Bundle\GridBundle\Sorter\ORM;

use Oro\Bundle\GridBundle\Sorter\SorterInterface;
use Oro\Bundle\GridBundle\Datagrid\ProxyQueryInterface;
use Oro\Bundle\GridBundle\Field\FieldDescriptionInterface;

class Sorter implements SorterInterface
{
    /**
     * Ascending sorting direction
     */
    const DIRECTION_ASC = "ASC";

    /**
     * Descending sorting direction
     */
    const DIRECTION_DESC = "DESC";

    /**
     * @var FieldDescriptionInterface
     */
    protected $field;

    /**
     * @var string
     */
    protected $direction;

    /**
     * {@inheritdoc}
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->field->getName();
    }

    /**
     * {@inheritdoc}
     */
    public function getDirection()
    {
        return $this->direction;
    }

    /**
     * {@inheritdoc}
     */
    public function setDirection($direction)
    {
        if (!is_null($direction)) {
            if (in_array($direction, array(self::DIRECTION_ASC, self::DIRECTION_DESC))) {
                $this->direction = $direction;
            } elseif ($direction) {
                $this->direction = self::DIRECTION_DESC;
            } else {
                $this->direction = self::DIRECTION_ASC;
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function initialize(FieldDescriptionInterface $field, $direction = null)
    {
        $this->field = $field;
        $this->setDirection($direction);
    }

    /**
     * @param ProxyQueryInterface $queryInterface
     * @param string $direction
     *
     * @return void
     */
    public function apply(ProxyQueryInterface $queryInterface, $direction = null)
    {
        $this->setDirection($direction);

        $alias = $this->field->getOption('entity_alias')
            ?: $queryInterface->entityJoin($this->getParentAssociationMappings());

        $queryInterface->getQueryBuilder()->addOrderBy(
            $this->getFieldNameAssociatedWithAlias($alias),
            $this->getDirection()
        );
    }

    /**
     * @param $alias
     * @return string
     */
    protected function getFieldNameAssociatedWithAlias($alias)
    {
        return sprintf('%s.%s', $alias, $this->field->getOption('field_name'));
    }

    /**
     * @return mixed
     */
    protected function getParentAssociationMappings()
    {
        return $this->getField()->getOption('parent_association_mappings', array());
    }
}
