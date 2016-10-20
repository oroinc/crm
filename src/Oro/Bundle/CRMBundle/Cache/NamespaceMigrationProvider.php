<?php

namespace Oro\Bundle\CRMBundle\Cache;

use Oro\Bundle\InstallerBundle\CacheWarmer\NamespaceMigrationProviderInterface;

class NamespaceMigrationProvider implements NamespaceMigrationProviderInterface
{
    /** @var string[] */
    protected $additionConfig
        = [
            'OroTaskBridgeBundle' => 'OroTaskCRMBridgeBundle',
            'OroCallBridgeBundle' => 'OroCallCRMBridgeBundle',
            'oro_report_'         => 'oro_reportcrm_',
            'Oro'                 => 'Oro',
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
