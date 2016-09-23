<?php

namespace Oro\Bundle\ChannelBundle\Entity\Manager;

use Oro\Bundle\SoapBundle\Entity\Manager\ApiEntityManager;

class ChannelApiEntityManager extends ApiEntityManager
{
    /**
     * {@inheritdoc}
     */
    protected function getSerializationConfig()
    {
        $config = [
            'excluded_fields' => ['data'],
            'fields'          => [
                'dataSource' => ['fields' => 'id'],
                'entities'   => ['fields' => 'name'],
                'status'     => [
                    'result_name' => 'active'
                ],
            ]
        ];

        return $config;
    }
}
