<?php

namespace OroCRM\Bundle\MagentoBundle\Provider;

use Oro\Bundle\IntegrationBundle\Provider\ChannelTypeInterface;

class MageChannelType implements ChannelTypeInterface
{
    public function getLabel()
    {
        return 'orocrm.magento.channel_type.label';
    }
}
