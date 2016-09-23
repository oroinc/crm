<?php

namespace Oro\Bundle\MagentoBundle\Entity;

use Oro\Bundle\IntegrationBundle\Entity\Channel as Integration;

interface IntegrationAwareInterface
{
    /**
     * @param Integration $integration
     * @return IntegrationAwareInterface
     */
    public function setChannel(Integration $integration);

    /**
     * @return Integration
     */
    public function getChannel();

    /**
     * @return string
     */
    public function getChannelName();
}
