<?php

namespace OroCRM\Bundle\CRMBundle\EventListener\Migration;

use Symfony\Component\Yaml\Yaml;

use Oro\Bundle\MigrationBundle\EventListener\ReleaseDataFixtureListener as BaseListener;

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
