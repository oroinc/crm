<?php

namespace Oro\Bundle\ChannelBundle\Entity\Repository;

use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;

/**
 * Represents Doctrine repository for Channel entity.
 */
interface ChannelRepositoryInterface
{
    /**
     * Returns channel names indexed by id
     *
     * @param AclHelper $aclHelper
     * @param           $type
     *
     * @return array
     */
    public function getAvailableChannelNames(AclHelper $aclHelper, $type = null);

    /**
     * @param array     $entities
     * @param bool      $status
     * @param AclHelper $aclHelper
     *
     * @return array
     */
    public function getChannelsByEntities(
        array $entities = [],
        $status = Channel::STATUS_ACTIVE,
        AclHelper $aclHelper = null
    );

    /**
     * @param array $entities
     * @param bool  $status
     *
     * @return QueryBuilder
     */
    public function getChannelsByEntitiesQB(array $entities = [], $status = Channel::STATUS_ACTIVE);

    /**
     * @param string $type
     *
     * @return QueryBuilder|null
     */
    public function getVisitsCountForChannelTypeQB($type);
}
