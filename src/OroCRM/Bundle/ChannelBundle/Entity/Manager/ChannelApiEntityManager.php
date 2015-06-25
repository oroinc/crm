<?php

namespace OroCRM\Bundle\ChannelBundle\Entity\Manager;

use Oro\Bundle\SoapBundle\Entity\Manager\ApiEntityManager;

class ChannelApiEntityManager extends ApiEntityManager
{
    /**
     * {@inheritdoc}
     */
    protected function getSerializationConfig()
    {
        $config = [
            'fields' => [
                'dataSource' => ['fields' => 'id'],
                'entities'   => ['fields' => 'name'],
                'status'     => [
                    'data_transformer' => 'orocrm_channel.channel_status_transformer'
                ],
            ]
        ];

        return $config;
    }
}
