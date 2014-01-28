<?php

namespace OroCRM\Bundle\MagentoBundle\Provider;

use Oro\Bundle\IntegrationBundle\Provider\ChannelInterface;

class ChannelType implements ChannelInterface
{
    const TYPE = 'magento';

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return 'orocrm.magento.channel_type.label';
    }
}
