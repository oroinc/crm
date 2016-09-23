<?php

namespace Oro\Bundle\ChannelBundle\Provider;

use Doctrine\ORM\QueryBuilder;

use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;
use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\ChannelBundle\Entity\Repository\ChannelRepository;

class ChannelsByEntitiesProvider
{
    /**
     * @var DoctrineHelper
     */
    protected $doctrineHelper;

    /**
     * @var ChannelRepository
     */
    protected $channelRepository;

    /**
     * @var array [{parameters_hash} => {Channel[]}, ...]
     */
    protected $channelsCache = [];

    /**
     * @var AclHelper
     */
    protected $aclHelper;

    /**
     * @param DoctrineHelper $doctrineHelper
     * @param AclHelper      $aclHelper
     */
    public function __construct(DoctrineHelper $doctrineHelper, AclHelper $aclHelper)
    {
        $this->doctrineHelper = $doctrineHelper;
        $this->aclHelper = $aclHelper;
    }

    /**
     * @param array $entities
     * @param bool  $status
     *
     * @return mixed
     */
    public function getChannelsByEntities(array $entities = [], $status = Channel::STATUS_ACTIVE)
    {
        sort($entities);
        $hash = md5(serialize([$entities, $status]));
        if (!isset($this->channelsCache[$hash])) {
            $this->channelsCache[$hash] = $this->getChannelRepository()
                ->getChannelsByEntities($entities, $status, $this->aclHelper);
        }

        return $this->channelsCache[$hash];
    }

    /**
     * @param array $entities
     * @param bool  $status
     *
     * @return QueryBuilder
     */
    public function getChannelsByEntitiesQB(array $entities = [], $status = Channel::STATUS_ACTIVE)
    {
        return $this->getChannelRepository()->getChannelsByEntitiesQB($entities, $status);
    }

    /**
     * @return ChannelRepository
     */
    protected function getChannelRepository()
    {
        if (null === $this->channelRepository) {
            $this->channelRepository = $this->doctrineHelper
                ->getEntityRepositoryForClass('OroChannelBundle:Channel');
        }

        return $this->channelRepository;
    }
}
