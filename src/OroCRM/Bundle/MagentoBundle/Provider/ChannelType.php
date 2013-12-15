<?php

namespace OroCRM\Bundle\MagentoBundle\Provider;

use Oro\Bundle\IntegrationBundle\Provider\ChannelTypeInterface;

class ChannelType implements ChannelTypeInterface
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
