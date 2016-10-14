<?php

namespace Oro\Bundle\CRMBundle\Cache;

use Oro\Bundle\InstallerBundle\CacheWarmer\NamespaceMigrationProviderInterface;

class NamespaceMigrationProvider implements NamespaceMigrationProviderInterface
{
    /** @var string[] */
    protected $additionConfig
        = [
            'OroCRMTaskBridgeBundle' => 'OroTaskCRMBridgeBundle',
            'OroCRMCallBridgeBundle' => 'OroCallCRMBridgeBundle',
            'OroCRM'                 => 'Oro',
            'orocrm'                 => 'oro'
        ];

    /**
     * (@inheritdoc}
     */
    public function getConfig()
    {
        return $this->additionConfig;
    }
}
