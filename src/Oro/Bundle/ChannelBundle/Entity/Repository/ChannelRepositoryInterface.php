<?php

namespace Oro\Bundle\ChannelBundle\Entity\Repository;

use Doctrine\ORM\QueryBuilder;

use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;
use Oro\Bundle\ChannelBundle\Entity\Channel;

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
     * @param \DateTime $start
     * @param \DateTime $end
     * @param AclHelper $aclHelper
     * @param string    $type
     *
     * @return integer
     *
     * @deprecated Deprecated since version 2.0, to be removed in 3.0.
     */
    public function getVisitsCountByPeriodForChannelType(
        \DateTime $start,
        \DateTime $end,
        AclHelper $aclHelper,
        $type
    );

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
