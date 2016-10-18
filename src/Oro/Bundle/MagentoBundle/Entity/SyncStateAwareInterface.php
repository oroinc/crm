<?php

namespace Oro\Bundle\MagentoBundle\Entity;

interface SyncStateAwareInterface
{
    const PROPERTY = 'syncState';

    /**
     * @return int
     */
    public function getSyncState();

    /**
     * @param int $syncState
     *
     * @return SyncStateAwareInterface
     */
    public function setSyncState($syncState);
}
