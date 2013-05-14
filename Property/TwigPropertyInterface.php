<?php

namespace Oro\Bundle\GridBundle\Property;

interface TwigPropertyInterface extends PropertyInterface
{
    /**
     * @param \Twig_Environment $environment
     * @return null
     */
    public function setEnvironment(\Twig_Environment $environment);
}
