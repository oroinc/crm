<?php

namespace OroCRM\Bundle\ChannelBundle\Provider;

interface MetadataInterface
{
    /**
     * @return array
     */
    public function getMetadataList();

    /**
     * @param string $integrationType
     *
     * @return array
     */
    public function getMetadataByIntegrationType($integrationType);
}
