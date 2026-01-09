<?php

namespace Oro\Bundle\ChannelBundle\Provider;

/**
 * Defines the contract for providing channel-specific metadata and configuration information.
 */
interface MetadataProviderInterface
{
    /**
     * @return array
     */
    public function getEntitiesMetadata();

    /**
     * @return array
     */
    public function getChannelTypeMetadata();
}
