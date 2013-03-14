<?php

namespace Oro\Bundle\GridBundle\Action;

interface ActionFactoryInterface
{
    /**
     * @param string $name
     * @param string $type
     * @param array  $options
     *
     * @return ActionInterface
     */
    public function create($name, $type, array $options = array());
}
