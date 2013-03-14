<?php

namespace Oro\Bundle\GridBundle\Action;

interface ActionInterface
{
    /**
     * Action types
     */
    const TYPE_REDIRECT = 'oro_grid_action_redirect';
    const TYPE_REST = 'oro_grid_action_rest';

    /**
     * Filter name
     *
     * @return string
     */
    public function getName();

    /**
     * Action type
     *
     * @return string
     */
    public function getType();

    /**
     * Action options (route, ACL resource etc.)
     *
     * @return array
     */
    public function getOptions();

    /**
     * @param string $name
     */
    public function setName($name);

    /**
     * @param array $options
     */
    public function setOptions(array $options);
}
