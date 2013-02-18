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
        return $this->getField()->getName();
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
    public function initialize(FieldDescriptionInterface $field, $direction = null)
    {
        $this->field = $field;

        if (!is_null($direction)) {
            $this->direction = $direction;
        }
    }

    /**
     * @param ProxyQueryInterface $queryInterface
     * @param string $direction
     *
     * @return void
     */
    public function apply(ProxyQueryInterface $queryInterface, $direction = null)
    {
        if (!is_null($direction)) {
            $this->direction = $direction;
        }

        $queryInterface->getQueryBuilder()->addOrderBy($this->getName(), $this->getDirection());
    }
}
