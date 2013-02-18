<?php

namespace Oro\Bundle\GridBundle\Sorter;

use Oro\Bundle\GridBundle\Field\FieldDescriptionInterface;

class SorterFactory implements SorterFactoryInterface
{

    /**
     * @param FieldDescriptionInterface $field
     * @param string $direction
     * @throws \RunTimeException
     *
     * @return SorterInterface
     */
    public function create(FieldDescriptionInterface $field, $direction = null)
    {
        if (!$field) {
            throw new \RunTimeException('The field name must be defined for sorter');
        }

        // TODO: remove this shitty piece
        $sorter = new \Oro\Bundle\GridBundle\Sorter\ORM\Sorter();

        $sorter->initialize($field, $direction);

        return $sorter;
    }
}
