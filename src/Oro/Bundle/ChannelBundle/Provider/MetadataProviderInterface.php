<?php

namespace Oro\Bundle\ChannelBundle\Provider;

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
