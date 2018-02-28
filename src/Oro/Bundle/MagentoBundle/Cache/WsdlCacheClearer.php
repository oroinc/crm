<?php

namespace Oro\Bundle\MagentoBundle\Cache;

use Oro\Bundle\MagentoBundle\Service\WsdlManager;
use Symfony\Component\HttpKernel\CacheClearer\CacheClearerInterface;

class WsdlCacheClearer implements CacheClearerInterface
{
    /**
     * @var WsdlManager
     */
    protected $wsdlManager;

    /**
     * @param WsdlManager $wsdlManager
     */
    public function __construct(WsdlManager $wsdlManager)
    {
        $this->wsdlManager = $wsdlManager;
    }

    /**
     * {@inheritdoc}
     */
    public function clear($cacheDir)
    {
        $this->wsdlManager->clearAllWsdlCaches();
    }
}
