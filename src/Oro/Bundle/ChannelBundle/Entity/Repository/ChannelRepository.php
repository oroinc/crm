<?php

namespace Oro\Bundle\ChannelBundle\Entity\Repository;

/**
 * The real implementation of this class is at \Oro\Bridge\MarketingCRM\Entity\Repository\ChannelRepository
 */
class ChannelRepository extends ChannelRepositoryAbstract
{
    #[\Override]
    public function getVisitsCountForChannelTypeQB($type)
    {
        return null;
    }
}
