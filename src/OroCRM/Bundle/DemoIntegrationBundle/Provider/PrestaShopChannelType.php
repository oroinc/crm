<?php

namespace OroCRM\Bundle\DemoIntegrationBundle\Provider;

use Oro\Bundle\IntegrationBundle\Provider\ChannelTypeInterface;

class PrestaShopChannelType implements ChannelTypeInterface
{
    public function getLabel()
    {
        return 'orocrm.demo_integration.prestashop.channel.label';
    }
}
