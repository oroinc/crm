<?php

namespace Oro\Bundle\ChannelBundle\Entity\Manager;

use Oro\Bundle\SoapBundle\Entity\Manager\ApiEntityManager;

/**
 * The API manager for Channel entity.
 */
class ChannelApiEntityManager extends ApiEntityManager
{
    /**
     * {@inheritdoc}
     */
    protected function getSerializationConfig()
    {
        $config = [
            'fields' => [
                'data'       => ['exclude' => true],
                'dataSource' => ['fields' => 'id'],
                'entities'   => ['fields' => 'name'],
                'active'     => ['property_path' => 'status']
            ]
        ];

        return $config;
    }
}
