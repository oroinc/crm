<?php

namespace Oro\Bundle\MagentoBundle\Exception;

class ExtensionRequiredException extends \LogicException implements Exception
{
    /**
     * {@inheritdoc}
     */
    public function __construct($message = 'Oro Bridge extension is not installed.')
    {
        parent::__construct($message);
    }
}
