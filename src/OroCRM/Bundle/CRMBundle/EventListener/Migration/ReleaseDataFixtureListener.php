<?php

namespace OroCRM\Bundle\CRMBundle\EventListener\Migration;

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

use Oro\Bundle\MigrationBundle\EventListener\ReleaseDataFixtureListener as BaseListener;

/**
 * TODO: This listener is a temporary solution for migration of data fixtures.
 * TODO: It should be removed in scope of https://magecore.atlassian.net/browse/BAP-3605
 */
class ReleaseDataFixtureListener extends BaseListener implements ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * {@inheritDoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * @return array
     */
    protected function getMappingData()
    {
        $filePath = $this->container
            ->get('kernel')
            ->locateResource('@OroCRMCRMBundle/EventListener/Migration/data/1.0.0/crm.yml');
        return Yaml::parse(realpath($filePath));
    }
}
