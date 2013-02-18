<?php

namespace Oro\Bundle\GridBundle\Sorter;

use Oro\Bundle\GridBundle\Datagrid\ProxyQueryInterface;
use Oro\Bundle\GridBundle\Field\FieldDescriptionInterface;

interface SorterInterface
{
    /**
     * @return string
     */
    public function getName();

    /**
     * @return string
     */
    public function getField();

    /**
     * @return string
     */
    public function getDirection();

    /**
     * @param FieldDescriptionInterface $field
     * @param string $direction
     *
     * @return void
     */
    public function initialize(FieldDescriptionInterface $field, $direction = null);

    /**
     * @param ProxyQueryInterface $queryInterface
     *
     * @return void
     */
    public function apply(ProxyQueryInterface $queryInterface);
}
