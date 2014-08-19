<?php

namespace OroCRM\Bundle\ChannelBundle\Provider;

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
