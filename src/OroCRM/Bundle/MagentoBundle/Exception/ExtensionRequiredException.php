<?php

namespace OroCRM\Bundle\MagentoBundle\Exception;

class ExtensionRequiredException extends \LogicException
{
    /**
     * {@inheritdoc}
     */
    public function __construct()
    {
        parent::__construct('orocrm.magento.exception.extension_required');
    }
}
