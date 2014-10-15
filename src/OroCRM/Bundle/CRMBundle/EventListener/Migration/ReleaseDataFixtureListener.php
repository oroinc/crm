<?php

namespace OroCRM\Bundle\CRMBundle\EventListener\Migration;

use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Yaml\Yaml;

use Oro\Bundle\MigrationBundle\EventListener\ReleaseDataFixtureListener as BaseListener;

/**
 * TODO: This listener is a temporary solution for migration of data fixtures.
 * TODO: It should be removed in scope of https://magecore.atlassian.net/browse/BAP-3605
 */
class ReleaseDataFixtureListener extends BaseListener
{
    /**
     * @var KernelInterface
     */
    protected $kernel;

    /**
     * @param KernelInterface $kernel
     */
    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * @return array
     */
    protected function getMappingData()
    {
        $filePath = $this->kernel->locateResource('@OroCRMCRMBundle/EventListener/Migration/data/1.0.0/crm.yml');

        return Yaml::parse(realpath($filePath));
    }
}
