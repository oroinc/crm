<?php

namespace Oro\Bundle\GridBundle\Action;

interface ActionUrlGeneratorInterface
{
    /**
     * @param string $routeName
     * @param array $parameters
     * @param array $placeholders
     * @return string
     */
    public function generate($routeName, array $parameters = array(), array $placeholders = array());
}
