<?php

namespace Oro\Bundle\MagentoBundle\Provider;

use Oro\Bundle\IntegrationBundle\Provider\ChannelInterface;
use Oro\Bundle\IntegrationBundle\Provider\IconAwareIntegrationInterface;

class Magento2ChannelType implements ChannelInterface, IconAwareIntegrationInterface
{
    const TYPE = 'magento2';

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return 'oro.magento.channel_type.magento2.label';
    }

    /**
     * {@inheritdoc}
     */
    public function getIcon()
    {
        return 'bundles/oromagento/img/magento-logo.png';
    }
}
