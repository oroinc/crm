<?php

namespace Oro\Bundle\MagentoBundle\Exception;

class AdminUrlRequiredException extends \LogicException implements Exception
{
    /**
     * {@inheritdoc}
     */
    public function __construct($message = 'Admin url is required.')
    {
        parent::__construct($message);
    }
}
