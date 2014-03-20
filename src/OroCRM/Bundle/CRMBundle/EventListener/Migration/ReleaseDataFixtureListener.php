<?php

namespace OroCRM\Bundle\CRMBundle\EventListener\Migration;

use Symfony\Component\Yaml\Yaml;

use Oro\Bundle\MigrationBundle\EventListener\ReleaseDataFixtureListener as BaseListener;

/**
 * TODO: This listener is a temporary solution for migration of data fixtures.
 * TODO: It should be removed in scope of https://magecore.atlassian.net/browse/BAP-3605
 */
class ReleaseDataFixtureListener extends BaseListener
{
    /**
     * @return array
     */
    protected function getMappingData()
    {
        return Yaml::parse(realpath(__DIR__ . '/data/1.0.0/crm.yml'));
    }
}
